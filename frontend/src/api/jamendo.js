import axios from "axios";

const jamendoApi = axios.create({
    baseURL: "https://api.jamendo.com/v3.0/",
    headers: {}, 
});

export default jamendoApi;