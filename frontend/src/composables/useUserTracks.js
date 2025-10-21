// src/composables/useUserTracks.js
import { ref } from "vue";
import backendApi from "@/api/backend";

export function useUserTracks() {
    const tracksCount = ref(0);
    const loading = ref(false);
    const error = ref(null);

    async function fetchTrackCount() {
        loading.value = true;
        try {
            const { data } = await backendApi.get("/profile-user-tracks.php");
            if (data.status === "success") {
                tracksCount.value = data.count;
            } else {
                throw new Error(data.message || "Unknown error");
            }
        } catch (e) {
            error.value = e.message;
        } finally {
            loading.value = false;
        }
    }

    return { tracksCount, loading, error, fetchTrackCount };
}
