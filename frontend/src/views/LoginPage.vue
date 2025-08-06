<template>
    <main class="login">
        <div class="container">
            <h1>Login Page</h1>
            <p class="body-text">
                Якщо Ви забули пароль перейдіть за 
                <router-link class="forget-link" to="/recover-password" text="посиланням"></router-link>
            </p>
        </div>
    </main>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

</script>

<style lang="scss" scoped>
.login {
    height: calc(100vh - 100px);
    height: calc(100dvh - 100px);
    padding: var(--m-space-80) 0;
    display: flex;
    align-items: center;

    .body-text {
        font-size: 14px;
    }
    .forget-link {
        cursor: pointer;
        color: var(--palette-fresh);
        text-decoration: underline;
    }
}
@media (max-width: 576px) {
    .login {
        height: calc(100vh - 120px);
        height: calc(100dvh - 120px);
        padding: var(--m-space-32) 0;
    }
}
</style>