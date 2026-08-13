import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import NouvelleDemande from './pages/NouvelleDemande';
import AdminDemandes from './pages/AdminDemandes';
import Home from './pages/Home';
import Information from './pages/Information';

function RouteProtegee({ children }) {
  const { utilisateur } = useAuth();
  return utilisateur ? children : <Navigate to="/connexion" replace />;
}

function AppRoutes() {
  return (
    <Routes>
      <Route path="/" element={<Home />} />
      <Route path="/information" element={<Information />} />
      <Route path="/connexion" element={<Login />} />
      <Route path="/inscription" element={<Register />} />
      <Route path="/tableau-de-bord" element={<RouteProtegee><Dashboard /></RouteProtegee>} />
      <Route path="/nouvelle-demande" element={<RouteProtegee><NouvelleDemande /></RouteProtegee>} />
      <Route path="/admin/demandes" element={<RouteProtegee><AdminDemandes /></RouteProtegee>} />
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}

export default function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <AppRoutes />
      </BrowserRouter>
    </AuthProvider>
  );
}