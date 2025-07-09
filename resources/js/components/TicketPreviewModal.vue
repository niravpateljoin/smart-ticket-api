<template>
    <div class="modal-overlay">
        <div class="modal ticket-preview__modal">
            <h3>Ticket Details</h3>
            <p><strong>Subject:</strong> {{ ticket.subject }}</p>
            <p><strong>Description:</strong> {{ ticket.body }}</p>
            <p><strong>Status:</strong> {{ ticket.status }}</p>
            <p><strong>Category:</strong> {{ ticket.category }}</p>
            <p><strong>Confidence:</strong> {{ ticket.confidence }}</p>
            <p><strong>Explanation:</strong> {{ ticket.explanation }}</p>
            <button class="close-btn" @click="$emit('close')">Close</button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        ticket: Object
    }
};
</script>

<style src="../assets/styles/ticket-preview.css" scoped></style>
