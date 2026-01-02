<?php

namespace Tests\Unit\Services\Peppol;

use App\Services\Peppol\PeppolDirectoryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PeppolDirectoryServiceTest extends TestCase
{
    protected PeppolDirectoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PeppolDirectoryService(testMode: true);
        Cache::flush();
    }

    public function test_can_be_instantiated(): void
    {
        $this->assertInstanceOf(PeppolDirectoryService::class, $this->service);
    }

    public function test_can_set_test_mode(): void
    {
        $service = new PeppolDirectoryService();
        $result = $service->setTestMode(true);

        $this->assertInstanceOf(PeppolDirectoryService::class, $result);
    }

    public function test_returns_supported_schemes(): void
    {
        $schemes = $this->service->getSupportedSchemes();

        $this->assertIsArray($schemes);
        $this->assertArrayHasKey('0208', $schemes);
        $this->assertArrayHasKey('9925', $schemes);
    }

    public function test_lookup_returns_correct_structure(): void
    {
        Http::fake([
            '*' => Http::response(['matches' => []], 200),
        ]);

        $result = $this->service->lookup('0123456789', '0208');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('found', $result);
        $this->assertArrayHasKey('source', $result);
    }

    public function test_lookup_caches_results(): void
    {
        Http::fake([
            '*' => Http::response(['matches' => []], 200),
        ]);

        $this->service->lookup('0123456789', '0208');
        $this->service->lookup('0123456789', '0208');

        // Only one HTTP request should be made due to caching
        Http::assertSentCount(1);
    }

    public function test_verify_belgian_vat_cleans_number(): void
    {
        Http::fake([
            '*' => Http::response(['matches' => []], 200),
        ]);

        $result = $this->service->verifyBelgianVat('BE 0123.456.789');

        $this->assertIsArray($result);
    }

    public function test_search_by_name_returns_results(): void
    {
        Http::fake([
            '*' => Http::response([
                'total' => 1,
                'matches' => [
                    [
                        'participantID' => '0208:0123456789',
                        'name' => 'Test Company',
                        'countryCode' => 'BE',
                        'registrationDate' => '2024-01-01',
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->searchByName('Test Company', 'BE');

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['total']);
        $this->assertCount(1, $result['matches']);
    }

    public function test_search_by_name_handles_errors(): void
    {
        Http::fake([
            '*' => Http::response('Server Error', 500),
        ]);

        $result = $this->service->searchByName('Test Company');

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_directory_lookup_parses_response(): void
    {
        Http::fake([
            '*' => Http::response([
                'matches' => [
                    [
                        'participantID' => '0208:0123456789',
                        'name' => 'Found Company',
                        'countryCode' => 'BE',
                        'registrationDate' => '2024-01-01',
                        'docTypes' => ['invoice'],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->lookupViaDirectory('0123456789', '0208');

        $this->assertTrue($result['found']);
        $this->assertEquals('directory', $result['source']);
        $this->assertEquals('Found Company', $result['name']);
        $this->assertEquals('BE', $result['country_code']);
    }

    public function test_directory_lookup_handles_not_found(): void
    {
        Http::fake([
            '*' => Http::response(['matches' => []], 200),
        ]);

        $result = $this->service->lookupViaDirectory('9999999999', '0208');

        $this->assertFalse($result['found']);
    }

    public function test_is_registered_returns_boolean(): void
    {
        Http::fake([
            '*' => Http::response([
                'matches' => [
                    ['participantID' => '0208:0123456789'],
                ],
            ], 200),
        ]);

        $result = $this->service->isRegistered('0123456789', '0208');

        $this->assertTrue($result);
    }

    public function test_is_registered_returns_false_when_not_found(): void
    {
        Http::fake([
            '*' => Http::response(['matches' => []], 200),
        ]);

        $result = $this->service->isRegistered('9999999999', '0208');

        $this->assertFalse($result);
    }
}
