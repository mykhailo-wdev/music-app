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
    h3 {
        text-align: center;
    }
}
</style>