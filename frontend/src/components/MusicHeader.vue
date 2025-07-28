<template>
    <header class="header-wrap">
        <div class="container">
            <div class="header-row">
                <div class="header-logo">
                    <img :src="require('@/assets/images/logo.svg')" alt="Logo">
                </div>
                <div class="header-btns">
                    <music-button type-btn="btn-fresh" text="Вхід"></music-button>
                    <music-button type-btn="btn-sky" text="Реєстрація"></music-button>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import MusicButton from './MusicButton.vue';

</script>

<style lang="scss">
.header-wrap {
    height: 100px;
    display: flex;
    align-items: center;
    background-color: var(--palette-light);
    border-bottom: 1px solid var(--palette-violet);

    .header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .header-btns {
        display: grid;
        grid-template-columns: auto auto;
        gap: 20px;
    }
}

@media (max-width: 576px) {
    .header-wrap {
        height: 120px;
        .header-row {
            flex-direction: column;
        }
        .header-logo {
            width: 100%;
            max-width: 80px;
            margin: 0 auto;
        }
        .header-btns {
            margin-top: 10px;
            gap: 10px;
        }
    }
}
</style>