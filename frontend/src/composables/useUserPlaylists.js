// src/composables/useUserPlaylists.js
import { ref } from "vue";
import backendApi from "@/api/backend";

export function useUserPlaylists() {
    const playlistCount = ref(0);
    const loading = ref(false);
    const error = ref(null);

    async function fetchPlaylistCount() {
        loading.value = true;
        try {
            const { data } = await backendApi.get("/profile-user-playlist.php");
            if (data.status === "success") {
                playlistCount.value = data.count;
            } else {
                throw new Error(data.message);
            }
        } catch (e) {
            error.value = e.message;
        } finally {
            loading.value = false;
        }
    }

    return { playlistCount, loading, error, fetchPlaylistCount };
    
}
