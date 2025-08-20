import axios from "axios";

let baseURL = "http://localhost:8000";

if (window.location.hostname !== "localhost") {
    baseURL = "https://api.mysite.com"; // прод-домен
}

const backendApi = axios.create({ baseURL });

backendApi.interceptors.request.use((config) => {
    const token = localStorage.getItem("token");
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default backendApi;
