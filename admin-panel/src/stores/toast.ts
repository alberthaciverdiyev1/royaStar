import { reactive } from 'vue'

export interface ToastMessage {
  type: 'success' | 'error'
  text: string
}

const state = reactive({
  message: null as ToastMessage | null,
  visible: false,
})

let timeout: ReturnType<typeof setTimeout> | null = null

export function showToast(msg: ToastMessage) {
  state.message = msg
  state.visible = true
  if (timeout) clearTimeout(timeout)
  timeout = setTimeout(() => {
    state.visible = false
    setTimeout(() => {
      state.message = null
    }, 300)
  }, 3000)
}

export function useToast() {
  return state
}
