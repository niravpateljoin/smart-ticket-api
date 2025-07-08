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
                <th>Subject</th>
                <th>Description</th>
                <th>Status</th>
                <th>Category</th>
                <th>AI Explanation</th>
                <th>Confidence</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="ticket in paginatedTickets" :key="ticket.id" class="ticket-list__row">
                <td>{{ ticket.subject }}</td>
                <td>{{ ticket.body }}</td>
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
                <td>{{ ticket.explanation }}</td>
                <td>
                  {{ (ticket.confidence || 0).toFixed(2) }}
                </td>
                <td>
                    <button class="ticket-list__btn ticket-list__btn--view" @click="goToDetail(ticket.id)">
                        View
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
    </div>
</template>

<script>
import TicketForm from '@/components/TicketForm.vue';
import axios from 'axios';

export default {
    name: 'TicketList',
    components: { TicketForm },
    data() {
        return {
            tickets: [],
            showForm: false,
            search: '',
            selectedCategory: '',
            currentPage: 1,
            perPage: 5,
            categories: ['bug', 'feature', 'question', 'other'],
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
        async createTicket(ticket) {
            await axios.post('/api/tickets', ticket);
            this.showForm = false;
            await this.fetchTickets();
        },
        goToDetail(id) {
            this.$router.push(`/tickets/${id}`);
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
        }
    }
};
</script>

<style src="../assets/styles/ticket-list.css" scoped></style>
