// src/store/modules/auth.js
import api from '@/api/backend';
const API = api;

function getIanaTz() {
    try { 
        return Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC'; 
    } catch { 
        return 'UTC'; 
    }
}

export default {
    state: () => ({
        user: null,
        token: localStorage.getItem('access_token') || localStorage.getItem('token') || null,
        status: null,
        error: null,
        authErrors: {},
    }),

    mutations: {
        setUser(state, user) { state.user = user; },
        setToken(state, token) { state.token = token; },
        setStatus(state, status) { state.status = status; },
        setAuthErrors(state, errors) { state.authErrors = errors; },
        setError(state, error) { state.error = error; },
        logout(state) {
            state.user = null;
            state.token = null;
            state.status = null;
            state.error = null;
            state.authErrors = {};
            localStorage.removeItem('access_token');
            localStorage.removeItem('token');
            localStorage.removeItem('refresh_token');
        },
        clearAuthErrors(state) { state.authErrors = {}; },
        auth_request(state) { 
            state.status = 'loading'; 
            state.authErrors = {}; 
        },
        auth_success(state) { state.status = 'success'; },
        auth_error(state, errors) { 
            state.status = 'error'; 
            state.authErrors = errors; 
        },
    },

    actions: {
        async register({ commit }, payload) {
            commit('setStatus', 'loading');
            commit('setError', null);
            try {
                const body = { ...payload, timezone: payload?.timezone || getIanaTz() };
                const res = await API.post('/register.php', body);
                if (res.data.status === 'success') {
                    commit('setStatus', 'success');
                } else {
                    commit('setStatus', 'error');
                    commit('setError', res.data.message);
                }
            } catch (err) {
                commit('setStatus', 'error');
                commit('setError', err.message);
            }
        },

        async login({ commit }, payload) {
            commit('setStatus', 'loading');
            commit('clearAuthErrors');
            try {
                const body = { ...payload, timezone: payload?.timezone || getIanaTz() };
                const res = await API.post('/login.php', body);

                if (res.data.status === 'success') {
                    const access = res.data.access_token;
                    const refresh = res.data.refresh_token;
                    localStorage.setItem('access_token', access);
                    localStorage.setItem('token', access);
                    localStorage.setItem('refresh_token', refresh);

                    const user = res.data.user || { email: payload.email, timezone: getIanaTz() };
                    commit('setUser', user);
                    commit('setToken', access);
                    commit('setStatus', 'success');
                } else {
                    commit('setStatus', 'error');
                    commit('setAuthErrors', res.data.errors || { general: res.data.message });
                }
            } catch (err) {
                commit('setStatus', 'error');
                commit('setAuthErrors', { general: err.message });
            }
        },

        async logout({ commit }) {
            try {
                const refresh_token = localStorage.getItem('refresh_token');
                await API.post('/logout.php', { refresh_token, timezone: getIanaTz() });
            } catch (error) {
                console.warn('Logout API failed:', error?.message || error);
            }
            commit('logout');
        },

        async forgotPassword({ commit }, payload) {
            commit('setStatus', 'loading');
            commit('clearAuthErrors');
            try {
                const res = await API.post('/forgot_password.php', {
                    ...payload,
                    timezone: getIanaTz(),
                });

                if (res.data.status === 'success') {
                    commit('setStatus', 'success');
                    return true;
                } else {
                    commit('setStatus', 'error');
                    commit('setAuthErrors', res.data.errors || { general: res.data.message });
                    return false;
                }
            } catch (err) {
                commit('setStatus', 'error');
                commit('setAuthErrors', { general: 'Помилка з’єднання з сервером' });
                return false;
            }
        },

        async verifyEmail({ commit }, token) {
            commit('auth_request');
            try {
                const res = await API.post('/verify_email.php', { token, timezone: getIanaTz() });
                if (res.data.status === 'success') {
                    const access = res.data.access_token;
                    const refresh = res.data.refresh_token;
                    localStorage.setItem('access_token', access);
                    localStorage.setItem('token', access);
                    localStorage.setItem('refresh_token', refresh);
                    commit('setToken', access);
                    if (res.data.user) commit('setUser', res.data.user);
                    commit('auth_success');
                    return { success: true };
                } else {
                    commit('auth_error', { general: res.data.message });
                    return { success: false, message: res.data.message };
                }
            } catch (err) {
                commit('auth_error', { general: err.message });
                return { success: false, message: err.message };
            }
        },
    },

    getters: {
        isAuthenticated: (state) => !!state.token,
        authStatus: (state) => state.status,
        authError: (state) => state.authErrors,
    },
};




