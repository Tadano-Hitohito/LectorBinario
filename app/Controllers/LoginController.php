<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class LoginController extends BaseController
{
    public function index()
    {
        return view('login');
    }

    public function autenticar()
    {
        $username = trim($this->request->getPost('username'));

        // Lista de usuarios permitidos (basado en notas.txt)
        $allowedUsers = ['alex', 'andres', 'scarlet', 'carlos', 'sofia'];

        // Verificamos si el usuario existe en la lista (sin contraseña)
        if (in_array(strtolower($username), $allowedUsers)) {
          
            $sessionData = [
                'id'         => time(), // ID temporal
                'username'   => $username,
                'nombre'     => ucfirst($username), // Capitalizamos el nombre para mostrarlo bonito
                'apellido'   => '',
                'isLoggedIn' => true,
            ];
            session()->set($sessionData);

          
            return redirect()->to('/analizar_roms');
        } else {
          
            return redirect()->to('/login')->with('error', 'Usuario no encontrado.');
        }
    }
    public function cerrar_sesion()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}