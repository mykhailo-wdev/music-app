<template>
    <div>
        <main class="home">
            <div class="container">
                <h3>Введіть назву пісні, щоб прослухати її</h3>
                <music-search :search-limit="2"></music-search>
            </div>
        </main>
        <music-footer></music-footer>
    </div>
</template>

<script setup>
import MusicButton from '@/components/MusicButton.vue';
import MusicSearch from '@/components/MusicSearch.vue';
import MusicFooter from '@/components/MusicFooter.vue';

</script>

<style lang="scss" scoped>
.home {
    height: calc(100vh - 140px);
    height: calc(100dvh - 140px);
    padding: var(--m-space-80) 0;
    h3 {
        text-align: center;
    }
}
@media (max-width: 576px) {
    .home {
        height: calc(100vh - 170px);
        height: calc(100dvh - 170px);
        padding: 16px 0;
        h3 {
            font-size: 16px;
        }
        .search-wrap {
            margin-top: 8px;
        }
        .body-text {
            margin-top: 0;
        }
    }
}
</style>