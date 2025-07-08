<template>
    <div>
        <header class="app__header">
            <h1 class="app__header--title">Smart Ticket Triage</h1>
            <div class="toggle-btn-container">
                <button @click="toggleTheme" class="toggle-btn">
                    Toggle {{ theme === 'light' ? 'Dark' : 'Light' }} Theme
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
