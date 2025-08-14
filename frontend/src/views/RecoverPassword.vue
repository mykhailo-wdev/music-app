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
.forgot {
    height: calc(100vh - 100px);
    height: calc(100dvh - 100px);
    padding: var(--m-space-80) 0;
}
@media (max-width: 576px) {
    .forgot {
        height: calc(100vh - 120px);
        height: calc(100dvh - 120px);
        padding: var(--m-space-32) 0;
    }
}
</style>