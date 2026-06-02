import { BrowserRouter } from 'react-router-dom'
import { AppProviders } from './app/providers/AppProviders'
import { VisibilityWorkbench } from './modules/access/components/VisibilityWorkbench'
import './App.css'

function App() {
  return (
    <AppProviders>
      <BrowserRouter>
        <VisibilityWorkbench />
      </BrowserRouter>
    </AppProviders>
  )
}

export default App
