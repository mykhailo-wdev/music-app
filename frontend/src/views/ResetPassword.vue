<template>
    <div>
        <h1>Відновлення паролю</h1>
            <form @submit.prevent="handleSubmit">
            <input type="password" v-model="password" placeholder="Новий пароль" />
            <button type="submit">Змінити пароль</button>
            </form>
            <p v-if="message">{{ message }}</p>
            <p v-if="error">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const password = ref('')
const message = ref('')
const error = ref('')
const token = ref('')
const apiBase = window.location.hostname === 'localhost'
  ? 'http://localhost:8000/api'
  : `${window.location.protocol}//${window.location.hostname}/api`


onMounted(() => {
    const params = new URLSearchParams(window.location.search)
    token.value = params.get('token') || ''
})

async function handleSubmit() {
    if (!token.value) {
        error.value = 'Токен відсутній'
        return
    }
    const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/
    if (!passwordPattern.test(password.value)) {
        error.value = 'Пароль має містити мінімум 8 символів, велику і малу літеру, цифру та спецсимвол'
        message.value = ''
        return
    }

    try {
        const res = await axios.post(`${apiBase}/reset_password.php`, {
            token: token.value,
            password: password.value
        })

        if (res.data.success) {
            message.value = res.data.message
            error.value = ''
        } else {
            error.value = res.data.errors.general || 'Помилка'
            message.value = ''
        }
    } catch (e) {
        error.value = 'Помилка з’єднання з сервером'
    }
}


</script>