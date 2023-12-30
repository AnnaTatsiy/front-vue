<script>

</script>

<template>
    <div>
        <nav class="navbar navbar-expand-lg bg-dark navbar-dark bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">SportClub</a>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                                    <router-link to="/login" class={setActive}>Расписание</router-link>
                                  </li>
                                  <li class="nav-item">
                       <!--                 <NavLink to="/customers" className={setActive}>Клиенты</NavLink> -->
                                  </li>
                                  <li class="nav-item">
                          <!--              <NavLink to="/coaches" className={setActive}>Тренеры</NavLink> -->
                                  </li>

                                  <li class="nav-item">
                         <!--               <NavLink to="/group-workouts" className={setActive}>Тренировки</NavLink>
                                      {!user &&
                                      <NavLink to={"/login"} className={setActive}>Вход</NavLink>
                                      }  -->
                                  </li>

                              </ul>
                              <!--               <form class="d-flex justify-content-end">
                                               {user &&
                                               <div class="d-lg-flex col-lg-3 justify-content-lg-end">
                                                 <button onClick={(e) => {
                                                   e.preventDefault();
                                                   logout();
                                                   }} className="btn btn-primary">Выход</button>
                                               </div>}
                                           </form> -->
                </div>
            </div>
        </nav>

    </div>
</template>

<style scoped>

</style>
