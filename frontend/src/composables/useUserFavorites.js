// src/composables/useUserFavorites.js
import { ref } from "vue";
import backendApi from "@/api/backend";

export function useUserFavorites() {
    const favoritesCount = ref(0);
    const loading = ref(false);
    const error = ref(null);

    async function fetchFavoritesCount() {
        loading.value = true;
        try {
            const { data } = await backendApi.get("/profile-user-favorites.php");
            if (data.status === "success") {
                favoritesCount.value = data.count;
            } else {
                throw new Error(data.message);
            }
        } catch (e) {
            error.value = e.message;
        } finally {
            loading.value = false;
        }
    }

    return { favoritesCount, loading, error, fetchFavoritesCount };
    
}
