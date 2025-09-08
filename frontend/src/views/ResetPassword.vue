<template>
    <main class="reset">
        <div class="container">
            <div class="form-group">
                <h2>Відновлення паролю</h2>
                <div v-if="!isLoading">
                    <form @submit.prevent="handleSubmit" :class="{ disabled: isFormDisabled }">
                        <fieldset :disabled="isFormDisabled">
                            <div class="wrap-inp">
                                <input 
                                    class="reset-data"  
                                    required
                                    v-model="password" 
                                    placeholder="Новий пароль" 
                                    maxlength="30"
                                    :type="showPassword ? 'text' : 'password'" 
                                />
                                <button type="button" class="toggle-pass" aria-label="Show password" @click="showPassword = !showPassword">
                                    <svg v-if="showPassword" class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg v-else class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                            <music-button type="submit" typeBtn="btn-fresh" text="Змінити пароль"></music-button>
                        </fieldset>
                    </form>
                </div>
                <div v-if="isLoading" class="loader"></div>
                <h4 class="message-info" v-if="message">{{ message }}</h4>
                <h4 class="message-info message-info--error" v-if="error">{{ error }}</h4>
            </div>
        </div>
    </main>
</template>

<script setup>
import MusicButton from '@/components/MusicButton.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const showPassword = ref(false);
const isLoading = ref(false);
const password = ref('');
const message = ref('');
const error = ref('');
const token = ref('');
const isFormDisabled = ref(false)
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

    isLoading.value = true
    message.value = ''
    error.value = ''

    try {
        const res = await axios.post(`${apiBase}/reset_password.php`, {
            token: token.value,
            password: password.value
        })

        setTimeout(() => {
            isLoading.value = false

            if (res.data.success) {
                message.value = res.data.message
                error.value = ''
                isFormDisabled.value = true
                setTimeout(() => {
                    router.push('/login')
                }, 3000)
            } else {
                error.value = res.data.errors.general || 'Помилка'
            }
        }, 2000)
    } catch (e) {
        setTimeout(() => {
            isLoading.value = false
            error.value = 'Помилка з’єднання з сервером'
        }, 2000)
    }
}



</script>

<style lang="scss" scoped>
@use '../assets/styles/mixins';
.reset {
    height: calc(100vh - 100px);
    height: calc(100dvh - 100px);
    padding: var(--m-space-80) 0;
    .form-group {
        max-width: 576px;
        margin: 0 auto;
    }
    input {
        @include mixins.body-text();
        display: inline-block;
        height: 40px;
        line-height: 40px;
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
        &:focus::-webkit-input-placeholder {
            color: transparent;
        }
        &:focus::-moz-placeholder {
            color: transparent;
        }
        &:focus:-moz-placeholder {
            color: transparent;
        }
        &:focus:-ms-input-placeholder {
            color: transparent;
        }
        &::placeholder {
            font-size: var(--fs-small);
        }
    }
    .wrap-inp {
        position: relative;
        margin: 8px 0 var(--m-space-16);
        .toggle-pass {
            cursor: pointer;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: 16px;
            width: 24px;
            height: 24px;
        }
    }
    .message-info {
        margin-top: var(--m-space-16);
        color: var(--palette-success);

    }
    .message-info--error {
        color: var(--palette-error);
    }
}
fieldset {
    border: none;        
    margin: 0;           
    padding: 0;          
    min-width: 0;        
}
fieldset:disabled {
    opacity: 0;
    display: none;       
    pointer-events: none; 
}
form.disabled {
    opacity: 0;
    display: none;       
    pointer-events: none; 
}

.loader {
    width: 100px;
    aspect-ratio: 1;
    display: grid;
    border: 4px solid #0000;
    border-radius: 50%;
    border-color: #ccc #0000;
    animation: l16 1s infinite linear;
}
.loader::before,
.loader::after {    
    content: "";
    grid-area: 1/1;
    margin: 2px;
    border: inherit;
    border-radius: 50%;
}
.loader::before {
    border-color: #f03355 #0000;
    animation: inherit; 
    animation-duration: .5s;
    animation-direction: reverse;
}
.loader::after {
    margin: 8px;
}
@keyframes l16 { 
    100%{transform: rotate(1turn)}
}
@media (max-width: 576px) {
    .reset {
        height: calc(100vh - 120px);
        height: calc(100dvh - 120px);
        padding: var(--m-space-32) 0;
    }
}
</style>