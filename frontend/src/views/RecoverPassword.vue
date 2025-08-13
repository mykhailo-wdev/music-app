<template>
    <main class="forgot">
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
const mode = ref('forgot')

const router = useRouter();

</script>

<style lang="scss" scoped>

</style>