<template>
    <div>
        <main class="player">
            <div class="container">
                <music-search :search-limit="null"></music-search>
            </div>
        </main>
        <music-footer></music-footer>
    </div>
</template>

<script setup>
import MusicSearch from '@/components/MusicSearch.vue';
import MusicFooter from '@/components/MusicFooter.vue';

</script>

<style lang="scss" scoped>
.player {
    height: calc(100vh - 140px);
    height: calc(100dvh - 140px);
    padding: var(--m-space-80) 0;
}
@media (max-width: 576px) {
    .player {
        padding: 32px 0 0;
        margin-bottom: var(--m-space-80);
    }
}
</style>