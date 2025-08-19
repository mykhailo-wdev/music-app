import axios from 'axios';

axios.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

const API_BASE_URL = process.env.VUE_APP_API_BASE_URL || 'http://localhost:8000/api';

export default {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
        status: null,
        error: null,
        authErrors: {},
    }),

    mutations: {
        setUser(state, user) {
            state.user = user;
        },
        setToken(state, token) {
            state.token = token;
        },
        setStatus(state, status) {
            state.status = status;
        },
        setAuthErrors(state, errors) {
            state.authErrors = errors;
        },
        setError(state, error) {
            state.error = error;
        },
        logout(state) {
            state.user = null;
            state.token = null;
            state.status = null;
            state.error = null;
            localStorage.removeItem('token');
            localStorage.removeItem('refresh_token'); 
        },
        clearAuthErrors(state) {
            state.authErrors = {};
        },
        auth_request(state) {
            state.status = 'loading';
            state.authErrors = {};
        },
        auth_success(state) {
            state.status = 'success';
        },
        auth_error(state, errors) {
            state.status = 'error';
            state.authErrors = errors;
        }
    },

    actions: {
        async register({ commit }, payload) {
            commit('setStatus', 'loading');
            commit('setError', null);
            try {
                const res = await axios.post(`${API_BASE_URL}/register.php`, payload);
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
            const res = await axios.post(`${API_BASE_URL}/login.php`, payload);

            if (res.data.status === 'success') {
                commit('setStatus', 'success');
                commit('setUser', res.data.user || payload.email);
                commit('setToken', res.data.access_token);
                localStorage.setItem('token', res.data.access_token);
                localStorage.setItem('refresh_token', res.data.refresh_token);
            } else {
                commit('setStatus', 'error');
                commit('setAuthErrors', res.data.errors || { general: res.data.message });
            }
            } catch (err) {
                commit('setStatus', 'error');
                commit('setAuthErrors', { general: err.message });
            }
        },

        async logout({ commit, state }) {
            try {
            await axios.post(`${API_BASE_URL}/logout.php`, {
                refresh_token: localStorage.getItem('refresh_token')
            }, {
                headers: {
                Authorization: `Bearer ${state.token}`
                }
            });
            } catch (error) {
            }
            commit('logout');
        },

        async forgotPassword({ commit }, payload) {
            commit('setStatus', 'loading');
            commit('clearAuthErrors');

            try {
                const res = await axios.post(`${API_BASE_URL}/forgot_password.php`, payload);

                if (res.data.status === 'success') {
                    commit('setStatus', 'success');
                } else {
                    commit('setStatus', 'error');
                    commit('setAuthErrors', res.data.errors || { general: res.data.message });
                }
            } catch (err) {
                commit('setStatus', 'error');
                commit('setAuthErrors', { general: 'Помилка з’єднання з сервером' });
            }
        },

        async verifyEmail({ commit }, token) {
            commit('auth_request');
            try {
                const res = await axios.post(`${API_BASE_URL}/verify_email.php`, { token });
                if (res.data.status === 'success') {
                    localStorage.setItem('token', res.data.access_token);
                    localStorage.setItem('refresh_token', res.data.refresh_token);
                    commit('setToken', res.data.access_token);
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
        }
    },

    getters: {
        isAuthenticated: (state) => !!state.user,
        authStatus: (state) => state.status,
        authError: (state) => state.authErrors,
    }
};
