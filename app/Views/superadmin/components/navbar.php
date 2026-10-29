  <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container">
          <a class="navbar-brand" href="<?= base_url('/superadmin/dashboard') ?>">
              <i class="fas fa-crown me-2"></i>Superadmin Panel
          </a>

          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
              <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="navbarNav">
              <ul class="navbar-nav me-auto">
                  <li class="nav-item">
                      <a class="nav-link active" href="<?= base_url('/superadmin/dashboard') ?>">
                          <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                      </a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" href="<?= base_url('/superadmin/events') ?>">
                          <i class="fas fa-calendar-alt me-1"></i>Events
                      </a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" href="<?= base_url('/superadmin/admins') ?>">
                          <i class="fas fa-users-cog me-1"></i>Admins
                      </a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" href="<?= base_url('/superadmin/superadmins') ?>">
                          <i class="fas fa-user-shield me-1"></i>Superadmins
                      </a>
                  </li>
              </ul>

              <ul class="navbar-nav">
                  <li class="nav-item dropdown">
                      <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                          data-bs-toggle="dropdown">
                          <i class="fas fa-user-circle me-1"></i><?= session()->get('username') ?>
                      </a>
                      <ul class="dropdown-menu">
                          <li><a class="dropdown-item" href="<?= base_url('/logout') ?>">
                                  <i class="fas fa-sign-out-alt me-2"></i>Logout
                              </a></li>
                      </ul>
                  </li>
              </ul>
          </div>
      </div>
  </nav>