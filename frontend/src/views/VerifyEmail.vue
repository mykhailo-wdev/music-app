<template>
    <main class="verify-email">
        <div class="container">
            <h4 v-if="status === 'loading'">Підтверджуємо email...</h4>
            <h4 v-if="status === 'success'">Email підтверджено! Перенаправлення...</h4>
            <p v-if="status === 'error'" class="body-text error">{{ errorMessage }}</p>
        </div>
    </main>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useStore } from 'vuex';

const store = useStore();
const route = useRoute();
const router = useRouter();

const status = ref('loading');
const errorMessage = ref('');

onMounted(async () => {
    const token = route.query.token;
    if (!token) {
        status.value = 'error';
        errorMessage.value = 'Невірне посилання для підтвердження email';
        return;
    }

    const result = await store.dispatch('verifyEmail', token);

    if (result.success) {
        status.value = 'success';
        setTimeout(() => {
            router.push('/player');
        }, 1000);
    } else {
        status.value = 'error';
        errorMessage.value = result.message || 'Помилка підтвердження email';
    }
});


const authStatus = computed(() => store.getters.authStatus);
</script>

<style lang="scss" scoped>
.verify-email {
    height: calc(100vh - 100px);
    height: calc(100dvh - 100px);
    padding: var(--m-space-80) 0;
    display: flex;
    justify-content: center;
    align-items: center;
    .error {
        color: var(--palette-error);
    }
    h4, p {
        text-align: center;
    }
}
</style>