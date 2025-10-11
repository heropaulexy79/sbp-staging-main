import { Editor } from "@tiptap/vue-3";
import { computed, reactive, toRefs } from "vue";

type EditorStore = {
    isImageUploadModalOpen: boolean;
};

const store = reactive<EditorStore>({
    isImageUploadModalOpen: false,
});

export function useEditorStore() {
    // const { isImageUploadModalOpen } = toRefs(store);
    const isImageUploadModalOpen = computed(() => store.isImageUploadModalOpen);

    function updateImageUploadModal(v: boolean) {
        store.isImageUploadModalOpen = v;
    }

    return {
        updateImageUploadModal,
        isImageUploadModalOpen,
    };
}
