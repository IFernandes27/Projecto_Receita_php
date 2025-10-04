<?php
// Puxa dados do utilizador para mostrar o nome do adm
$id = $_SESSION['id_utilizador'];
$sql = "SELECT * FROM utilizador WHERE id_utilizador = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();


?>

<!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      <!--begin::Header-->
      <nav class="app-header navbar navbar-expand bg-body">
        <!--begin::Container-->
        <div class="container-fluid">
          <!--begin::Start Navbar Links-->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                <i class="bi bi-list"></i>
              </a>
            </li>
            <li class="nav-item d-none d-md-block"><a href="adm1.php" class="nav-link">Inicio</a></li>
          
          </ul>
          <!--end::Start Navbar Links-->
          <!--begin::End Navbar Links-->
          <ul class="navbar-nav ms-auto">
           
        
            
            <!--end::Fullscreen Toggle-->
            <!--begin::User Menu Dropdown-->
            <li class="nav-item dropdown user-menu">
              
                <span class="d-none d-md-inline"><?= htmlspecialchars($user['nome']) ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <!--begin::User Image-->
                <li class="user-header text-bg-primary">
                  <img
                    src="./assets/img/user2-160x160.jpg"
                    class="rounded-circle shadow"
                    alt="User Image"
                  />
                  <p>
                    Alexander Pierce - Web Developer
                    <small>Member since Nov. 2023</small>
                  </p>
                </li>
                <!--end::User Image-->
                <!--begin::Menu Body-->
                <li class="user-body">
                  <!--begin::Row-->
                  <div class="row">
                    <div class="col-4 text-center"><a href="#">Followers</a></div>
                    <div class="col-4 text-center"><a href="#">Sales</a></div>
                    <div class="col-4 text-center"><a href="#">Friends</a></div>
                  </div>
                  <!--end::Row-->
                </li>
                <!--end::Menu Body-->
                <!--begin::Menu Footer-->
                <li class="user-footer">
                  <a href="#" class="btn btn-default btn-flat">Profile</a>
                  <a href="#" class="btn btn-default btn-flat float-end">Sign out</a>
                </li>
                <!--end::Menu Footer-->
              </ul>
            </li>
            <!--end::User Menu Dropdown-->
          </ul>
          <!--end::End Navbar Links-->
        </div>
        <!--end::Container-->
      </nav>
      <!--end::Header-->
      <!--begin::Sidebar-->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="adm1.php" class="brand-link">
            <!--begin::Brand Image-->
            <img
              src="./assets/img/AdminLTELogo.png"
              alt="AdminLTE Logo"
              class="brand-image opacity-75 shadow"
            />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">Area do administrador</span>
            <!--end::Brand Text-->
          </a>
          <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->
        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              role="navigation"
              aria-label="Main navigation"
              data-accordion="false"
              id="navigation"
            >
              


            
              <li class="nav-item">
                <a href="adm1.php" class="nav-link">
                  <i class="bi bi-shield-lock"></i>
                  <p>Administração</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="ver_utilizadores.php" class="nav-link">
                  <i class="bi bi-person"></i>
                  <p>Utilizadores</p>
                </a>
              </li>

               <li class="nav-item">
                <a href="ver_receitas.php" class="nav-link">
                  <i class="nav-icon bi bi-journal-text"></i>
                  <p>Receitas</p>
                </a>
              </li>

               <li class="nav-item">
                <a href="ver_encomendas.php" class="nav-link">
                  <i class="nav-icon bi bi-bag-check"></i>
                  <p>Encomendas</p>
                </a>
              </li>

               <li class="nav-item">
                <a href="ver_categorias.php" class="nav-link">
                 <i class="nav-icon bi bi-tags"></i>
                  <p>Categorias</p>
                </a>
              </li>

              

               <li class="nav-item">
                <a href="../index.php" class="nav-link">
                  <i class="bi bi-house"></i>
                  <p>Sabores da CPLP</p>
                </a>
              </li>



              


<li class="nav-item">
                <a href="../logout.php" class="nav-link">
                  <i class="bi bi-box-arrow-right me-2"></i>
                  <p>Terminar sessão</p>
                </a>
              </li>



              
            
        
            






             
              
             
              
            </ul>
            <!--end::Sidebar Menu-->
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>
      <!--end::Sidebar-->