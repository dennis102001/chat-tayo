import { ref } from "vue"

export function useToast(){
    const toast = ref({
        show: false,
        message: '',
        type: 'Info'
    })

    function showToast(message, type = 'Info') {
        toast.value = {
            show: true,
            message,
            type
        }

        setTimeout(() => {
            toast.value.show = false
        }, 2500)
    }
    
    return {
        toast,
        showToast
    }
}