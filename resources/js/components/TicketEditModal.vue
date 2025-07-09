<template>
    <div class="modal-overlay">
        <div class="modal">
            <h3>Edit Ticket</h3>

            <label>Subject:</label>
            <input v-model="localTicket.subject" type="text" />

            <label>Description:</label>
            <textarea v-model="localTicket.body" rows="3" />

            <label>Status:</label>
            <select v-model="localTicket.status">
                <option value="open">Open</option>
                <option value="pending">Pending</option>
                <option value="closed">Closed</option>
            </select>

            <label>Category:</label>
            <select v-model="localTicket.category">
                <option value="">Uncategorized</option>
                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
            </select>

            <div class="ticket-edit__actions">
                <button @click="$emit('close')">Cancel</button>
                <button @click="submitEdit">Save</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        ticket: Object,
        categories: Array
    },
    data() {
        return {
            localTicket: { ...this.ticket }
        };
    },
    methods: {
        submitEdit() {
            this.$emit('submit', this.localTicket);
        }
    }
};
</script>

<style src="../assets/styles/ticket-edit.css" scoped></style>
