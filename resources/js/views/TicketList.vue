<template>
    <div class="ticket-list">
        <h2 class="ticket-list__title">Tickets</h2>

        <!-- Filters -->
        <div class="ticket-list__filters">
            <input
                type="text"
                placeholder="Search tickets..."
                v-model="search"
                class="ticket-list__search"
            />
            <select v-model="selectedCategory" class="ticket-list__select">
                <option value="">All Categories</option>
                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
            </select>
        </div>

        <!-- Actions -->
        <div class="ticket-list__actions">
            <button @click="showForm = true" class="ticket-list__btn">➕ New Ticket</button>
            <button @click="downloadCsv" class="ticket-list__btn ticket-list__btn--export">Export CSV</button>
        </div>

        <!-- Table -->
        <table class="ticket-list__table" v-if="paginatedTickets.length">
            <thead>
            <tr class="ticket-list__header">
                <th class="ticket-list__header--subject">Subject</th>
                <th class="ticket-list__header--description">Description</th>
                <th>Status</th>
                <th>Category</th>
                <th>AI Explanation</th>
                <th>Confidence</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="ticket in paginatedTickets" :key="ticket.id" class="ticket-list__row">
                <td class="ticket-list__row--subject">{{ ticket.subject }}</td>
                <td class="ticket-list__row--description">{{ ticket.body }}</td>
                <td>{{ ticket.status }}</td>
                <td>
                    <select
                        class="ticket-list__select"
                        v-model="ticket.category"
                        @change="confirmCategoryChange($event, ticket.id)"
                    >
                        <option value="">Uncategorized</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                    </select>
                </td>
                <td class="explanation__container">
                    <i class="fas fa-info-circle explanation__icon"></i>
                    <span class="explanation__icon-text">
                        {{ ticket.explanation }}
                    </span>
                </td>
                <td>
                  {{ (ticket.confidence || 0).toFixed(2) }}
                </td>
                <td>
                    <button class="ticket-list__btn ticket-list__btn--view" @click="openPreview(ticket)">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="ticket-list__btn ticket-list__btn--edit" @click="openEdit(ticket)">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="ticket-list__btn ticket-list__btn--delete" @click="confirmDeleteTicket($event, ticket.id)">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button class="ticket-list__btn ticket-list__btn--classify" @click="classify(ticket)" :disabled="classifyingId === ticket.id">
                        <span v-if="classifyingId === ticket.id">
                            <i class="fas fa-spinner fa-spin"></i>classifying...
                        </span>
                        <span v-else>
                            Classify
                        </span>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="ticket-list__pagination" v-if="totalPages > 1">
            <button
                v-for="page in totalPages"
                :key="page"
                @click="currentPage = page"
                :class="{ active: page === currentPage }"
            >
                {{ page }}
            </button>
        </div>

        <TicketForm
            v-if="showForm"
            @close="showForm = false"
            @submit="createTicket"
        />
        <TicketPreviewModal
            v-if="previewTicket"
            :ticket="previewTicket"
            @close="closePreview"
        />
        <TicketEditModal
            v-if="editTicketData"
            :ticket="editTicketData"
            :categories="categories"
            @close="closeEdit"
            @submit="submitEdit"
        />
    </div>
</template>

<script>
import TicketForm from '@/components/TicketForm.vue';
import TicketPreviewModal from '@/components/TicketPreviewModal.vue';
import TicketEditModal from '@/components/TicketEditModal.vue';
import axios from 'axios';

export default {
    name: 'TicketList',
    components: { TicketForm, TicketPreviewModal, TicketEditModal },
    data() {
        return {
            tickets: [],
            showForm: false,
            search: '',
            selectedCategory: '',
            currentPage: 1,
            perPage: 5,
            categories: ['bug', 'feature', 'question', 'other'],
            previewTicket: null,
            editTicketData: null,
            classifyingId: null,
        };
    },
    computed: {
        filteredTickets() {
            if (!Array.isArray(this.tickets)) return [];

            return this.tickets.filter(ticket => {
                const matchesSearch =
                    ticket.subject?.toLowerCase().includes(this.search.toLowerCase()) ||
                    ticket.body?.toLowerCase().includes(this.search.toLowerCase());
                const matchesCategory = this.selectedCategory
                    ? ticket.category === this.selectedCategory
                    : true;
                return matchesSearch && matchesCategory;
            });
        },
        totalPages() {
            return Math.ceil(this.filteredTickets.length / this.perPage);
        },
        paginatedTickets() {
            const start = (this.currentPage - 1) * this.perPage;
            return Array.isArray(this.filteredTickets)
                ? this.filteredTickets.slice(start, start + this.perPage)
                : [];
        }
    },
    watch: {
        search() {
            this.currentPage = 1;
        },
        selectedCategory() {
            this.currentPage = 1;
        }
    },
    mounted() {
        this.fetchTickets();
    },
    methods: {
        async fetchTickets() {
            const res = await axios.get('/api/tickets');
            this.tickets = res.data.data || res.data;
        },
        async createTicket(ticket) {
            await axios.post('/api/tickets', ticket);
            this.showForm = false;
            await this.fetchTickets();
        },
        async confirmCategoryChange(event, ticketId) {
            const newCategory = event.target.value;
            const confirmed = window.confirm(`Are you sure you want to change the category to "${newCategory}"?`);

            if (confirmed) {
                await this.updateCategory(ticketId, newCategory);
            } else {
                // Revert to original value (refetch or cancel selection)
                this.fetchTickets();
            }
        },
        async updateCategory(ticketId, category) {
            await axios.patch(`/api/tickets/${ticketId}`, { category });
            await this.fetchTickets();
        },
        openPreview(ticket) {
            this.previewTicket = ticket;
        },
        closePreview() {
            this.previewTicket = null;
        },
        async confirmDeleteTicket(event, ticketId) {
            const confirmed = window.confirm("Are you sure you want to remove the ticket?");
            if (confirmed) {
                await axios.delete(`/api/tickets/${ticketId}`);
            }
            this.fetchTickets();
        },
        openEdit(ticket) {
            this.editTicketData = { ...ticket };
        },
        closeEdit() {
            this.editTicketData = null;
        },
        async submitEdit(updatedTicket) {
            console.log(updatedTicket)
            try {
                await axios.patch(`/api/tickets/${updatedTicket.id}`, {
                    subject: updatedTicket.subject,
                    body: updatedTicket.body,
                    status: updatedTicket.status,
                    category: updatedTicket.category
                });
                this.editTicketData = null;
                await this.fetchTickets();
            } catch (error) {
                console.error("Error updating ticket:", error);
            }
        },
        async classify(ticket) {
            this.classifyingId = ticket.id;
            try {
                await axios.post(`/api/tickets/${ticket.id}/classify`);
                await new Promise(resolve => setTimeout(resolve, 1000));
                alert('Classification successful!');
                await this.fetchTickets();
            } catch (e) {
                alert('Classification failed. Please try again.');
            } finally {
                this.classifyingId = null;
            }
        },
        downloadCsv() {
            if (!this.tickets.length) return;
            const headers = ['ID', 'Subject', 'Body', 'Status', 'Category', 'Confidence'];
            const rows = this.tickets.map(ticket => [
                ticket.id,
                `"${ticket.subject}"`,
                `"${ticket.body}"`,
                ticket.status,
                ticket.category,
                ticket.confidence
            ]);
            const csvContent =
                [headers.join(','), ...rows.map(row => row.join(','))].join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.setAttribute('download', 'tickets.csv');
            link.click();
        },
    }
};
</script>

<style src="../assets/styles/ticket-list.css" scoped></style>
