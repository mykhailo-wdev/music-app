<template>
    <div class="auth-form">
        <h2 v-if="mode === 'login'">Вхід</h2>
        <h2 v-else-if="mode === 'register'">Реєстрація</h2>
        <h2 v-else-if="mode === 'forgot'">Відновлення паролю</h2>

        <div v-if="!isLoading && !showSuccessMessage && !showSuccessForgot">
            <form  @submit.prevent="handleSubmit" autocomplete="on">
                <div v-if="mode === 'register'" class="form-group">
                    <label for="name">Ім'я</label>
                    <input v-model="formData.name" id="name" type="text" required placeholder="Введіть Ваше Ім'я"  maxlength="30"/>
                    <small v-if="errors.name" class="error-text">{{ errors.name }}</small>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input v-model="formData.email" id="email" type="email" required placeholder="Введіть Вашу електронну пошту" maxlength="50"/>
                    <small v-if="errors.email" class="error-text">{{ errors.email }}</small>                    
                </div>

                <div v-if="mode !== 'forgot'" class="form-group">
                    <label for="password">Пароль</label>
                    <input 
                        v-model="formData.password" 
                        :type="showPassword ? 'text' : 'password'" 
                        id="password"  
                        class="password-visible" 
                        required 
                        placeholder="Введіть Пароль" 
                        maxlength="30"
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
                    <small v-if="errors.password" class="error-text">{{ errors.password }}</small>
                </div>

                <small v-if="generalError" class="error-text">{{ generalError }}</small>

                <div v-if="mode === 'register'" class="form-group">
                    <label for="passwordConfirm">Підтвердити пароль</label>
                    <input 
                        v-model="formData.passwordConfirm" 
                        id="passwordConfirm" 
                        class="password-visible" 
                        :type="showPasswordConfirm ? 'text' : 'password'" 
                        required  
                        placeholder="Повторіть пароль"
                        maxlength="30"
                    />
                    <button type="button" class="toggle-pass" aria-label="Show password Confirm" @click="showPasswordConfirm = !showPasswordConfirm">
                        <svg v-if="showPasswordConfirm" class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg v-else class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                    <small v-if="errors.passwordConfirm" class="error-text">{{ errors.passwordConfirm }}</small>
                </div>

                <music-button 
                    type-btn="btn-sky" 
                    :text="submitText"  
                    @action="handleSubmit"
                ></music-button>

            </form>

            <div class="auth-links" v-if="mode === 'login'">
                <router-link 
                    class="forget-link" 
                    to="/recover-password" 
                    text="Забули пароль?"
                >
                </router-link>
            </div>
            <div 
                class="auth-links" 
                v-else-if="mode === 'register'"
                >
                <router-link 
                    class="login-link" 
                    to="/login"
                    text="Вже є акаунт? Увійти"   
                >
                </router-link>
            </div>

            <div class="auth-links" 
                v-else-if="mode === 'forgot'"
            >
                <router-link 
                    class="login-link" 
                    to="/login"
                    text="Назад до входу"  
                >
                </router-link>
            </div>
        </div>

        <div v-if="isLoading" class="loader"></div>

        <div v-if="showSuccessMessage" class="success-message">
            <h3>Залишилось зовсім небагато. Для підтвердження рестрації, перейдуть у Вашу елетронну пошту та підтвердіть реєстрацію</h3>
        </div>

        <div v-if="showSuccessForgot" class="success-message">
            <h3>Ми відправили на Вашу електронну пошту повідомлення з відновленням паролю</h3>
        </div>

    </div>
</template>

<script setup>
import MusicButton from './MusicButton.vue';
import { ref, reactive, computed } from 'vue';
import { nextTick } from 'vue';
import { useStore } from 'vuex';
import { useRoute } from 'vue-router';
import { useRouter } from 'vue-router';
const showPassword = ref(false);
const showPasswordConfirm = ref(false);
const store = useStore();

const route = useRoute();
const router = useRouter();

const isLoading = ref(false);
const showSuccessMessage = ref(false);
const generalError = ref('');
const showSuccessForgot = ref(false);


const props = defineProps({
    mode: {
        type: String,
        default: 'login', 
    }
})

const emit = defineEmits(['change-mode'])
const formData = reactive({
    name: '',
    email: '',
    password: '',
    passwordConfirm: ''
})

const errors = reactive({
    name: '',
    email: '',
    password: '',
    passwordConfirm: ''
});


const authStatus = computed(() => store.getters.authStatus);
const authError = computed(() => store.getters.authError);

function validateForm() {
    let isValid = true;
    Object.keys(errors).forEach(key => errors[key] = '');

    if (props.mode === 'register' && !formData.name.trim()) {
        errors.name = 'Введіть ім’я';
        isValid = false;
    }

    if (!formData.email.trim()) {
        errors.email = 'Введіть email';
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
        errors.email = 'Невірний формат email';
        isValid = false;
    }

    if (props.mode !== 'forgot') {
        if (!formData.password) {
            errors.password = 'Введіть пароль';
            isValid = false;
        } else if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/.test(formData.password)) {
            errors.password = 'Пароль має містити мінімум 8 символів, велику і малу літеру, цифру та спецсимвол';
            isValid = false;
        }
    }

    if (props.mode === 'register') {
        if (!formData.passwordConfirm) {
            errors.passwordConfirm = 'Підтвердіть пароль';
            isValid = false;
        } else if (formData.password !== formData.passwordConfirm) {
            errors.passwordConfirm = 'Паролі не збігаються';
            isValid = false;
        }
    }

    return isValid;
}

async function handleSubmit() {
    if (!validateForm()) return;

    isLoading.value = true;
    showSuccessMessage.value = false;

    Object.keys(errors).forEach(key => errors[key] = '');
    generalError.value = '';

    try {
        if (props.mode === 'login') {
            await store.dispatch('login', {
                email: formData.email,
                password: formData.password
            });

            if (store.getters.authStatus === 'success') {
                router.push('/player');
            } else {
                const errObj = store.getters.authError || {};

                errors.email = errObj.email?.trim() || '';
                errors.password = errObj.password?.trim() || '';

                if (!errors.email && !errors.password && errObj.general?.trim()) {
                    generalError.value = errObj.general;
                } else {
                    generalError.value = '';
                }

                isLoading.value = false;
                return;
            }
        } else if (props.mode === 'register') {
            await store.dispatch('register', { 
                name: formData.name,
                email: formData.email, 
                password: formData.password 
            });

            if (store.getters.authStatus === 'success') {
                isLoading.value = true;
                setTimeout(() => {
                    isLoading.value = false;
                    showSuccessMessage.value = true;

                    setTimeout(() => {
                        showSuccessMessage.value = false;
                        resetForm();
                    }, 2000);
                }, 2000);

            } else {
                const errObj = store.getters.authError || {};
                errors.email = errObj.email || '';
                errors.password = errObj.password || '';
                errors.name = errObj.name || '';
                isLoading.value = false;
                return;
            }
        }  else if (props.mode === 'forgot') {
            const success = await store.dispatch('forgotPassword', { 
                email: formData.email 
            });

            isLoading.value = false;

            if (success) {
                showSuccessForgot.value = true;
                setTimeout(() => {
                    showSuccessForgot.value = false;
                    resetForm();
                    router.push('/login');
                }, 3000);
            } else {
                const errObj = store.getters.authError || {};
                errors.email = errObj.email || '';
                generalError.value = errObj.general || '';
            }
        }
    } catch (e) {
        console.error(e);
        isLoading.value = false;
    }
}

function resetForm() {
    formData.name = '';
    formData.email = '';
    formData.password = '';
    formData.passwordConfirm = '';
    Object.keys(errors).forEach(key => errors[key] = '');
}

const submitText = computed(() => {
    switch (props.mode) {
        case 'login': return 'Увійти';
        case 'register': return 'Зареєструватись';
        case 'forgot': return 'Відновити пароль';
        default: return 'Надіслати';
    }
});  
</script>

<style lang="scss" scoped>
@use '../assets/styles/mixins';
.auth-form {
    max-width: 576px;
    margin: 0 auto;
}
.form-group {
    margin-bottom: 12px;
    position: relative;
}
label {
    display: block;
    @include mixins.body-text();
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 4px;
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
.auth-links {
    margin-top: var(--m-space-16);
}
.toggle-pass {
    cursor: pointer;
    position: absolute;
    right: 16px;
    top: 30px;
}
.error-text {
    @include mixins.text-small();
    color: var(--palette-red);
    margin: 2px 0;
    display: block;
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
</style>
