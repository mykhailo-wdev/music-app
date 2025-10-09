// src/api/backend.js
import axios from "axios";

const isLocal = window.location.hostname === "localhost";
const backendApi = axios.create({
    baseURL: isLocal ? "http://localhost:8000/api" : "https://api.mysite.com/api",
    // withCredentials: true,
});

function getIanaTz() {
    try {
        return Intl.DateTimeFormat().resolvedOptions().timeZone || "UTC";
    } catch {
        return "UTC";
    }
}

backendApi.interceptors.request.use((config) => {
    const token = localStorage.getItem("access_token") || localStorage.getItem("token");
    if (token) config.headers.Authorization = `Bearer ${token}`;

    config.headers["X-Timezone"] = getIanaTz();

    const url = (config.url || "").toLowerCase();
    const isAuthEndpoint = [
        "/login.php",
        "/register.php",
        "/verify_email.php",
        "/refresh_token.php",
        "/logout.php",
        "/forgot_password.php",
    ].some((p) => url.endsWith(p));

    if (isAuthEndpoint) {
        if (config.data && typeof config.data === "object" && !Array.isArray(config.data)) {
            config.data.timezone ??= getIanaTz();
        } else if (config.method?.toLowerCase() === "post" && !config.data) {
            config.data = { timezone: getIanaTz() };
        }
        config.headers["Content-Type"] = "application/json";
    }

    return config;
});

let isRefreshing = false;
let queue = [];
const enqueue = (cb) => queue.push(cb);
const flushQueue = (error, token = null) => {
    queue.forEach((cb) => cb(error, token));
    queue = [];
};

backendApi.interceptors.response.use(
    (r) => r,
    async (error) => {
        const original = error.config;
        if (!original) return Promise.reject(error);

        if (original.url && original.url.includes("/refresh_token.php")) {
            return Promise.reject(error);
        }

        const isAuthError =
            error.response?.status === 401 ||
            /expired token|invalid token/i.test(error.response?.data?.message || "");

        if (!isAuthError) return Promise.reject(error);

        if (original._retry) return Promise.reject(error);
        original._retry = true;

        if (isRefreshing) {
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
            const { data } = await backendApi.post("/refresh_token.php", {
                refresh_token: refresh,
                timezone: getIanaTz(),
            });

            if (data.status !== "success" || !data.access_token) {
                throw new Error(data.message || "Refresh failed");
            }

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


