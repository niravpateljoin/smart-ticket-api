import { createRouter, createWebHistory } from 'vue-router';
import TicketList from '../views/TicketList.vue';
import Dashboard from '../views/Dashboard.vue';

const routes = [
    { path: '/', redirect: '/tickets' },
    { path: '/tickets', component: TicketList },
    { path: '/dashboard', component: Dashboard },
];

export default createRouter({
    history: createWebHistory(),
    routes,
});
