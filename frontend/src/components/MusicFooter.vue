<template>
    <footer>
        <div class="footer-wrap">
            <router-link to="/privacy-policy" class="privacy-link">Privacy Policy</router-link>
        </div>
    </footer>
</template>

<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();

</script>

<style lang="scss" scoped>
@use '../assets/styles/mixins';
.footer-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 40px;
    background-color: var(--palette-light);
}
.privacy-link {
    cursor: pointer;
    @include mixins.text-small();
    color: var(--palette-sky);
    &:hover {
        color: var(--palette-sky--hover);
        text-decoration: underline;
    }
    &:active {
        color: var(--palette-sky--active);
        text-decoration: underline;
    }
}
</style>