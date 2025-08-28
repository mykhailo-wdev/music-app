import axios from 'axios';

const API_BASE_URL = process.env.VUE_APP_API_BASE_URL || 'http://localhost:8000/api';
axios.defaults.baseURL = API_BASE_URL;

axios.interceptors.request.use((config) => {
    const access = localStorage.getItem('access_token') || localStorage.getItem('token');
    if (access) config.headers.Authorization = `Bearer ${access}`;
    return config;
});


let isRefreshing = false;
let queue = [];
const enqueue = (resolver) => queue.push(resolver);
const flush = (error, token) => {
    queue.forEach((resolve) => resolve(error, token));
    queue = [];
};

axios.interceptors.response.use(
    (res) => res,
    async (error) => {
        const original = error.config;
        if (!original) return Promise.reject(error);
        if (original.url && original.url.includes('/refresh_roken.php')) {
            return Promise.reject(error);
        }

        const isAuthError =
        error.response?.status === 401 ||
        /expired token|invalid token/i.test(error.response?.data?.message || '');

        if (!isAuthError) return Promise.reject(error);

        if (original._retry) return Promise.reject(error);
        original._retry = true;

        if (isRefreshing) {
            return new Promise((resolve, reject) => {
                enqueue((err, newToken) => {
                if (err) return reject(err);
                if (newToken) original.headers.Authorization = `Bearer ${newToken}`;
                resolve(axios(original));
                });
            });
        }

        isRefreshing = true;
        const refresh = localStorage.getItem('refresh_token');
        if (!refresh) {
            isRefreshing = false;
            flush(new Error('No refresh token'), null);
            return Promise.reject(error);
        }

        try {
            const { data } = await axios.post('/refresh_roken.php', { refresh_token: refresh }, {
                headers: { 'Content-Type': 'application/json' },
            });

        if (data.status !== 'success' || !data.access_token) {
            throw new Error(data.message || 'Refresh failed');
        }

        localStorage.setItem('access_token', data.access_token);
        localStorage.setItem('token', data.access_token);
        if (data.refresh_token) localStorage.setItem('refresh_token', data.refresh_token);

        original.headers.Authorization = `Bearer ${data.access_token}`;
        flush(null, data.access_token);
        return axios(original);
        } catch (e) {
            localStorage.removeItem('access_token');
            localStorage.removeItem('token');
            localStorage.removeItem('refresh_token');
            flush(e, null);
            return Promise.reject(e);
        } finally {
            isRefreshing = false;
        }
    }
);

export default {
    state: () => ({
        user: null,
        token: localStorage.getItem('access_token') || localStorage.getItem('token') || null,
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
            state.authErrors = {};
            localStorage.removeItem('access_token');
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
                const access = res.data.access_token;
                const refresh = res.data.refresh_token;
                localStorage.setItem('access_token', access);
                localStorage.setItem('token', access);
                localStorage.setItem('refresh_token', refresh);

                commit('setUser', res.data.user || payload.email);
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
                console.warn('Logout API failed:', error.message);
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
        }
    },

    getters: {
        // isAuthenticated: (state) => !!state.user,
        isAuthenticated: (state) => !!state.token,
        authStatus: (state) => state.status,
        authError: (state) => state.authErrors,
    }
};
