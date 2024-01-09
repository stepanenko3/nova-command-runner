import CommandRunnerDangerButton from './components/DangerButton'
import Tool from './pages/Tool'

Nova.booting((app, store) => {
  Nova.component('CommandRunnerDangerButton', CommandRunnerDangerButton)
  Nova.inertia('NovaCommandRunner', Tool)
})
