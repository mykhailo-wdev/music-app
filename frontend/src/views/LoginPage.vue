<template>
    <div>
        <main class="login">
            <div class="container">
                <music-auth-form 
                    :mode="mode" 
                    @change-mode="mode = $event"
                ></music-auth-form>
            </div>
        </main>
        <music-footer></music-footer>
    </div>
    
</template>

<script setup>
import MusicAuthForm from '@/components/MusicAuthForm.vue';
import MusicFooter from '@/components/MusicFooter.vue';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
const mode = ref('login')

const router = useRouter();

</script>

<style lang="scss" scoped>
.login {
    height: calc(100vh - 140px);
    height: calc(100dvh - 140px);
    padding: var(--m-space-80) 0;
}
@media (max-width: 576px) {
    .login {
        height: calc(100vh - 170px);
        height: calc(100dvh - 170px);
        padding: 16px 0;
    }
}
</style>