import Alpine from 'alpinejs';

Alpine.data('chatWidget', () => ({
    open: false,
    loading: false,
    messages: [],
    currentMessage: '',
    conversationId: null,
    unreadCount: 0,

    async init() {
        // Load last conversation from localStorage
        const lastConvId = localStorage.getItem('lastConversationId');
        if (lastConvId) {
            await this.loadConversation(lastConvId);
        }
    },

    toggle() {
        this.open = !this.open;
        if (this.open) {
            this.unreadCount = 0;
            this.$nextTick(() => this.scrollToBottom());
        }
    },

    async loadConversation(id) {
        try {
            const response = await axios.get(`/api/chat/conversations/${id}`);
            this.messages = response.data.messages.map(msg => ({
                ...msg,
                timestamp: new Date(msg.created_at),
            }));
            this.conversationId = id;
        } catch (error) {
            console.error('Error loading conversation:', error);
            // If conversation not found, clear localStorage
            if (error.response?.status === 404) {
                localStorage.removeItem('lastConversationId');
                this.conversationId = null;
            }
        }
    },

    async sendMessage() {
        if (!this.currentMessage.trim() || this.loading) return;

        // Add user message to UI immediately
        const userMessage = {
            id: Date.now(), // temporary ID
            role: 'user',
            content: this.currentMessage,
            created_at: new Date().toISOString(),
            timestamp: new Date(),
        };
        this.messages.push(userMessage);

        const messageText = this.currentMessage;
        this.currentMessage = '';
        this.loading = true;
        this.scrollToBottom();

        try {
            const response = await axios.post('/api/chat/send', {
                conversation_id: this.conversationId,
                message: messageText,
            });

            // Update conversation ID
            this.conversationId = response.data.conversation_id;
            localStorage.setItem('lastConversationId', this.conversationId);

            // Add assistant response
            this.messages.push({
                id: response.data.message_id,
                role: 'assistant',
                content: response.data.response,
                tool_calls: response.data.tool_calls || [],
                created_at: response.data.timestamp,
                timestamp: new Date(response.data.timestamp),
            });

            this.scrollToBottom();

        } catch (error) {
            console.error('Chat error:', error);

            // Show error in chat
            this.messages.push({
                id: Date.now(),
                role: 'assistant',
                content: 'Désolé, une erreur est survenue. Veuillez réessayer.',
                error: true,
                created_at: new Date().toISOString(),
                timestamp: new Date(),
            });

            // Also show toast if available
            if (window.showToast) {
                window.showToast('Erreur lors de l\'envoi du message', 'error');
            }

        } finally {
            this.loading = false;
        }
    },

    async confirmTool(executionId) {
        try {
            await axios.post(`/api/chat/tools/${executionId}/confirm`);
            // Reload conversation to get updated messages
            if (this.conversationId) {
                await this.loadConversation(this.conversationId);
            }
        } catch (error) {
            console.error('Tool confirmation error:', error);
            if (window.showToast) {
                window.showToast('Erreur lors de la confirmation', 'error');
            }
        }
    },

    scrollToBottom() {
        this.$nextTick(() => {
            const messagesEl = this.$refs.messages;
            if (messagesEl) {
                messagesEl.scrollTop = messagesEl.scrollHeight;
            }
        });
    },

    handleKeydown(event) {
        // Send on Enter (without Shift)
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            this.sendMessage();
        }
    },
}));
