   <?php

   test('halaman utama mengalihkan ke daftar ekskul', function () {
       $this->get('/')->assertRedirect(route('ekstrakurikuler.index'));
   });