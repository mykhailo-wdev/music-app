// src/composables/useUser.js
import { ref, onMounted } from "vue";
import backendApi from "@/api/backend";

export function useUser() {
    const user = ref(null);
    const loading = ref(false);
    const error = ref(null);

    async function fetchMe() {
        loading.value = true;
        try {
            const { data } = await backendApi.get("/profile-user-data.php");
            if (data.status === "success") {
                user.value = data.user;
            } else {
                throw new Error(data.message || "Unknown error");
            }
        } catch (e) {
            error.value = e.message;
        } finally {
            loading.value = false;
        }
    }

    onMounted(fetchMe);

    return { user, loading, error, fetchMe };
}
