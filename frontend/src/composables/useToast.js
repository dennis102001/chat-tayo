import { ref } from "vue"

const toast = ref({
    show: false,
    message: '',
    type: 'Info'
})

let toastTimeout

export function useToast(){
    
    function showToast(message, type = 'Info') {
        clearTimeout(toastTimeout)
        
        toast.value = {
            show: true,
            message,
            type
        }

        toastTimeout = setTimeout(() => {
            toast.value.show = false
        }, 2500)
    }
    
    return {
        toast,
        showToast
    }
}