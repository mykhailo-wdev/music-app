<template>
    <main class="main-app">
        <div class="container">
            <h3>Введіть назву пісні, щоб прослухати її</h3>
            <music-search :search-limit="2"></music-search>
        </div>
    </main>
</template>

<script setup>
import MusicButton from '@/components/MusicButton.vue';
import MusicSearch from '@/components/MusicSearch.vue';

</script>

<style lang="scss" scoped>
.main-app {
    height: calc(100vh - 100px);
    height: calc(100dvh - 100px);
    padding: var(--m-space-80) 0;

    h3 {
        text-align: center;
    }
}
@media (max-width: 576px) {
    .main-app {
        height: calc(100vh - 120px);
        height: calc(100dvh - 120px);
        padding: var(--m-space-32) 0;
    }
}
</style>