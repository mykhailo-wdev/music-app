<template>
    <main class="main-app">
        <div class="container">
            <music-button type-btn="btn-hot" text="Click"></music-button>

            <music-button  type-btn="btn-fresh" text="Click"></music-button>

            <music-button  type-btn="btn-sunny" text="Click"></music-button>

            <music-button  type-btn="btn-sky" text="Click"></music-button>

            <img :src="require('@/assets/images/screenshot.png')" alt="Скріншот">

        </div>
    </main>
</template>

<script setup>
import MusicButton from '@/components/MusicButton.vue';

</script>

<style lang="scss" scoped>
.main-app {
    height: calc(100vh - 100px);
    height: calc(100dvh - 100px);
    padding: var(--m-space-80) 0;
}
@media (max-width: 576px) {
    .main-app {
        height: calc(100vh - 120px);
        height: calc(100dvh - 120px);
        padding: var(--m-space-32) 0;
    }
}
</style>