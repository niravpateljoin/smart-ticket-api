<template>
    <div>
        <header class="app__header">
            <h1 class="app__header--title">Smart Ticket Triage</h1>
            <div class="toggle-btn-container">
                <button @click="toggleTheme" class="toggle-btn">
                    <span class="toggle-btn__icon">
                        <i :class="theme === 'light' ? 'fas fa-moon' : 'fas fa-sun'"></i>
                    </span>
                    <span class="toggle-btn__label" >{{ theme === 'light' ? 'Dark' : 'Light' }}</span>
                </button>
            </div>
        </header>

        <nav class="app__nav">
            <router-link to="/tickets">Tickets</router-link>
            <router-link to="/dashboard">Dashboard</router-link>
        </nav>

        <main class="app__main">
            <router-view />
        </main>
    </div>
</template>

<script>
export default {
    data() {
        return {
            theme: localStorage.getItem('theme') || 'light',
        };
    },
    methods: {
        toggleTheme() {
            this.theme = this.theme === 'light' ? 'dark' : 'light';
            localStorage.setItem('theme', this.theme);
            document.documentElement.classList.remove('light', 'dark');
            document.documentElement.classList.add(this.theme);
        }
    },
    mounted() {
        document.documentElement.classList.add(this.theme);
    }
};
</script>
