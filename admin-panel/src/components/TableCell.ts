import { defineComponent, type VNode } from 'vue'

export default defineComponent({
  name: 'TableCell',
  props: {
    render: { type: Function, required: true },
    item: { type: null, required: true },
  },
  render(this: any) {
    return this.render(this.item) as VNode | string | number
  },
})
