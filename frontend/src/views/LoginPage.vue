<template>
    <main class="login">
        <div class="container">
            <music-auth-form 
                :mode="mode" 
                @change-mode="mode = $event"
            ></music-auth-form>
        </div>
    </main>
</template>

<script setup>
import MusicAuthForm from '@/components/MusicAuthForm.vue';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
const mode = ref('login')

const router = useRouter();

</script>

<style lang="scss" scoped>

</style>