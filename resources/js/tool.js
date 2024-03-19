import Tool from './pages/Tool.vue'
import '../css/tool.css'

Nova.booting(() => {
  Nova.inertia('NovaCommandRunner', Tool)
})
