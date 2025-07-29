<template>
    <main class="not-found">
        <div class="container">
            <div class="not-found-img">
                <img :src="require('@/assets/images/404.webp')" alt="Сторінку не знайдено">
            </div>
            <h1>404</h1>
            <h4>Цю сторінку не знайдено</h4>
            <music-button type-btn="btn-hot" text="Повернутись на Головну" to="/" />
        </div>
    </main>
</template>

<script setup>
import MusicButton from '@/components/MusicButton.vue';
</script>

<style lang="scss" scoped>
.not-found {
    height: calc(100vh - 100px);
    height: calc(100dvh - 100px);
    padding: var(--m-space-80) 0;
    display: flex;
    align-items: center;
    justify-content: center;

    .not-found-img {
        width: 30%;
        margin: 0 auto;
    }

    .container, h1, h4 {
        text-align: center;
    }

    h4 {
        margin-bottom: var(--m-space-32);
    }
}
@media (max-width: 576px) {
    .not-found {
        height: calc(100vh - 120px);
        height: calc(100dvh - 120px);
        padding: var(--m-space-32) 0;

        .not-found-img {
            width: 60%;
        }
    }
}
</style>