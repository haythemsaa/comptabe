@extends('layouts.app')

@section('title', 'Configuration du paiement')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="text-center mb-4">
                <h3>Configurer le paiement</h3>
                <p class="text-muted">Ajoutez un moyen de paiement pour votre abonnement</p>
            </div>

            <div class="card">
                <div class="card-body">
                    <!-- Plan Summary -->
                    @if(isset($plan))
                    <div class="bg-light rounded p-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">{{ $plan->name }}</h5>
                                <small class="text-muted">Abonnement mensuel</small>
                            </div>
                            <div class="text-end">
                                <h4 class="mb-0">{{ number_format($plan->price, 2, ',', ' ') }} €</h4>
                                <small class="text-muted">/mois</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Stripe Elements Form -->
                    <form id="payment-form" action="{{ route('subscription.process-payment') }}" method="POST">
                        @csrf

                        @if(isset($plan))
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Titulaire de la carte</label>
                            <input type="text" class="form-control" id="cardholder-name" required
                                   placeholder="Nom sur la carte">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Informations de carte</label>
                            <div id="card-element" class="form-control py-3">
                                <!-- Stripe Elements placeholder -->
                            </div>
                            <div id="card-errors" class="text-danger small mt-1"></div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    J'accepte les <a href="#" target="_blank">conditions générales</a>
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submit-button">
                                <i class="ti ti-lock me-1"></i>
                                <span id="button-text">Confirmer et payer</span>
                                <span id="spinner" class="d-none">
                                    <span class="spinner-border spinner-border-sm" role="status"></span>
                                    Traitement...
                                </span>
                            </button>
                            <a href="{{ route('subscription.plans') }}" class="btn btn-outline-secondary">
                                Annuler
                            </a>
                        </div>
                    </form>

                    <!-- Security Notice -->
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="ti ti-shield-check me-1"></i>
                            Paiement sécurisé par Stripe
                        </small>
                    </div>
                </div>
            </div>

            <!-- FAQ -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="mb-3">Questions fréquentes</h6>
                    <div class="accordion accordion-flush" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Puis-je annuler à tout moment ?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Oui, vous pouvez annuler votre abonnement à tout moment. Vous conserverez l'accès jusqu'à la fin de la période facturée.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Mes données sont-elles sécurisées ?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Nous utilisons Stripe pour le traitement des paiements. Vos informations de carte ne sont jamais stockées sur nos serveurs.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('{{ config("services.stripe.key") }}');
const elements = stripe.elements();

const style = {
    base: {
        fontSize: '16px',
        color: '#32325d',
        '::placeholder': { color: '#aab7c4' }
    },
    invalid: {
        color: '#dc3545',
        iconColor: '#dc3545'
    }
};

const cardElement = elements.create('card', { style: style });
cardElement.mount('#card-element');

cardElement.on('change', function(event) {
    const displayError = document.getElementById('card-errors');
    displayError.textContent = event.error ? event.error.message : '';
});

const form = document.getElementById('payment-form');
form.addEventListener('submit', async function(event) {
    event.preventDefault();

    const submitButton = document.getElementById('submit-button');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');

    submitButton.disabled = true;
    buttonText.classList.add('d-none');
    spinner.classList.remove('d-none');

    const { paymentMethod, error } = await stripe.createPaymentMethod({
        type: 'card',
        card: cardElement,
        billing_details: {
            name: document.getElementById('cardholder-name').value
        }
    });

    if (error) {
        document.getElementById('card-errors').textContent = error.message;
        submitButton.disabled = false;
        buttonText.classList.remove('d-none');
        spinner.classList.add('d-none');
    } else {
        const hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'payment_method');
        hiddenInput.setAttribute('value', paymentMethod.id);
        form.appendChild(hiddenInput);
        form.submit();
    }
});
</script>
@endpush
