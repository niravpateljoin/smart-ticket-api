<template>
    <div v-if="ticket">
        <div class="modal-overlay" @click.self="goBack">
            <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
                <h2 id="modal-title" class="modal__title">{{ ticket.subject }}</h2>
                <div class="modal__body">
                    <p class="modal__body-text">{{ ticket.body }}</p>

                    <label for="category-select">Category:</label>
                    <select id="category-select" v-model="localCategory" @change="markDirty">
                        <option value="">Uncategorized</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                    </select>

                    <label for="note-textarea">Internal Note:</label>
                    <textarea id="note-textarea" v-model="localNote" @input="markDirty" rows="4" placeholder="Add internal note..."></textarea>

                    <div class="modal__explanation">
                        <strong>AI Explanation:</strong>
                        <p>{{ ticket.explanation || 'N/A' }}</p>
                        <p><strong>Confidence:</strong> {{ (ticket.confidence || 0).toFixed(2) }}</p>
                    </div>
                </div>

                <div class="modal__actions">
                    <button @click="save" :disabled="!dirty || saving">
                        <span v-if="saving">Saving...</span>
                        <span v-else>Save</span>
                    </button>

                    <button @click="classify" :disabled="classifying">
                        <span v-if="classifying">Classifying...</span>
                        <span v-else>Run Classification</span>
                    </button>

                    <button @click="goBack">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'TicketModelDetail',
    data() {
        return {
            ticket: null,
            categories: ['bug', 'feature', 'question', 'other'],
            localCategory: '',
            localNote: '',
            dirty: false,
            saving: false,
            classifying: false,
        };
    },
    mounted() {
        this.fetchTicket();
    },
    methods: {
        async fetchTicket() {
            const res = await axios.get(`/api/tickets/${this.$route.params.id}`);
            this.ticket = res.data;
            this.localCategory = this.ticket.category || '';
            this.localNote = this.ticket.note || '';
        },
        markDirty() {
            this.dirty = true;
        },
        async save() {
            if (!this.dirty) return;
            this.saving = true;
            await axios.patch(`/api/tickets/${this.ticket.id}`, {
                category: this.localCategory,
                note: this.localNote,
            });
            this.saving = false;
            this.dirty = false;
            await this.fetchTicket();
        },
        async classify() {
            this.classifying = true;
            try {
                await axios.post(`/api/tickets/${this.ticket.id}/classify`);
                await new Promise(resolve => setTimeout(resolve, 2000)); // simulate delay
                alert('Classification successful!');
                await this.fetchTicket();
            } catch (e) {
                alert('Classification failed. Please try again.');
            } finally {
                this.classifying = false;
            }
        },
        goBack() {
            this.$router.push('/tickets');
        }
    }
};
</script>

<style src="../assets/styles/ticket-modal.css" scoped></style>
