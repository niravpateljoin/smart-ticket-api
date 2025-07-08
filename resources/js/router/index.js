import { createRouter, createWebHistory } from 'vue-router';
import TicketList from '../views/TicketList.vue';
import TicketModelDetail from '../views/TicketModelDetail.vue';
import Dashboard from '../views/Dashboard.vue';

const routes = [
    { path: '/', redirect: '/tickets' },
    { path: '/tickets', component: TicketList },
    { path: '/tickets/:id', component: TicketModelDetail },
    { path: '/dashboard', component: Dashboard },
];

export default createRouter({
    history: createWebHistory(),
    routes,
});
