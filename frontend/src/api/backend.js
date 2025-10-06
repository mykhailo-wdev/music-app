import axios from "axios";

let baseURL = "http://localhost:8000";

if (window.location.hostname !== "localhost") {
    baseURL = "https://api.mysite.com"; // прод-домен
}

const backendApi = axios.create({ baseURL });

// ======================
// REQUEST INTERCEPTOR
// ======================
backendApi.interceptors.request.use((config) => {
    const token = localStorage.getItem("token"); // або access_token
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// ======================
// RESPONSE INTERCEPTOR
// ======================
let isRefreshing = false;
let queue = [];

const enqueue = (cb) => queue.push(cb);
const flushQueue = (error, token = null) => {
    queue.forEach((cb) => cb(error, token));
    queue = [];
};

backendApi.interceptors.response.use(
    (response) => response,
    async (error) => {
        const original = error.config;
        if (!original) return Promise.reject(error);

        // Якщо ми вже на refresh_token endpoint — не зациклюємо
        if (original.url.includes("/refresh_token.php")) return Promise.reject(error);

        // Перевірка, чи помилка через токен
        const isAuthError =
            error.response?.status === 401 ||
            /expired token|invalid token/i.test(error.response?.data?.message || "");

        if (!isAuthError) return Promise.reject(error);

        if (original._retry) return Promise.reject(error);
        original._retry = true;

        if (isRefreshing) {
            // Чекаємо, поки інший запит оновить токен
            return new Promise((resolve, reject) => {
                enqueue((err, newToken) => {
                    if (err) return reject(err);
                    if (newToken) original.headers.Authorization = `Bearer ${newToken}`;
                    resolve(backendApi(original));
                });
            });
        }

        isRefreshing = true;
        const refresh = localStorage.getItem("refresh_token");
        if (!refresh) {
            isRefreshing = false;
            flushQueue(new Error("No refresh token"));
            return Promise.reject(error);
        }

        try {
            const { data } = await axios.post(`${baseURL}/refresh_token.php`, {
                refresh_token: refresh,
            }, {
                headers: { "Content-Type": "application/json" },
            });

            if (data.status !== "success" || !data.access_token) {
                throw new Error(data.message || "Refresh failed");
            }

            // Зберігаємо нові токени
            localStorage.setItem("access_token", data.access_token);
            localStorage.setItem("token", data.access_token);
            if (data.refresh_token) localStorage.setItem("refresh_token", data.refresh_token);

            original.headers.Authorization = `Bearer ${data.access_token}`;
            flushQueue(null, data.access_token);

            return backendApi(original);
        } catch (e) {
            localStorage.removeItem("access_token");
            localStorage.removeItem("token");
            localStorage.removeItem("refresh_token");
            flushQueue(e, null);
            return Promise.reject(e);
        } finally {
            isRefreshing = false;
        }
    }
);

export default backendApi;
