<template>
    <div class="modal-overlay" @click.self="close" :class="theme">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="new-ticket-title">
            <h2 id="new-ticket-title" class="modal__title">New Ticket</h2>
            <form @submit.prevent="submit">
                <label for="subject">Subject</label>
                <input
                    id="subject"
                    type="text"
                    v-model.trim="subject"
                    required
                    maxlength="255"
                    autocomplete="off"
                    :disabled="submitting"
                />

                <label for="body">Body</label>
                <textarea
                    id="body"
                    v-model.trim="body"
                    required
                    rows="5"
                    :disabled="submitting"
                    placeholder="Describe the issue..."
                ></textarea>

                <div class="modal__actions">
                    <button type="submit" :disabled="submitting || !subject || !body">
                        <span v-if="submitting">Submitting...</span>
                        <span v-else>Submit</span>
                    </button>
                    <button type="button" @click="close" :disabled="submitting">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TicketForm',
    data() {
        return {
            subject: '',
            body: '',
            submitting: false,
        };
    },
    methods: {
        close() {
            this.$emit('close');
        },
        async submit() {
            if (!this.subject || !this.body) return;
            this.submitting = true;
            try {
                await this.$emit('submit', {
                    subject: this.subject,
                    body: this.body
                });
                this.subject = '';
                this.body = '';
                this.close();
            } finally {
                this.submitting = false;
            }
        }
    }
};
</script>

<style src="../assets/styles/ticket-form.css" scoped></style>
