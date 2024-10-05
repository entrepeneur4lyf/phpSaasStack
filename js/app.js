document.addEventListener('alpine:init', () => {
    Alpine.data('app', () => ({
        message: 'Welcome to AI Inference Service',
        updateMessage() {
            this.message = 'Empowering your projects with cutting-edge AI technology';
        }
    }));
});