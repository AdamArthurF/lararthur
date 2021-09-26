<script>
    let $insert

    $(() => {
        /**
         * Keperluan store pengaduan
         */
        // ================================================== //
        $insert = async (form) => {
            if (!grecaptcha.getResponse()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    html: "Recaptcha wajib dicentang!",
                })
                return;
            }

            Swal.fire({
                title: 'Apakah anda yakin untuk mengirim pengaduan?',
                text: "Pastikan data yang terisi sudah benar!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, kirim!'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    loading()

                    let formData = new FormData(form)

                    axios.post("{{ route('frontend.home.pengaduan.insert') }}", formData)
                        .then(res => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: res.data.message,
                                showConfirmButton: false,
                                timer: 1500
                            })
                        }).catch(err => {
                            console.error(err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                // html: err.response.data.message,
                                text: err.response.statusText
                            })
                        }).then(() => {
                            $('#form-pengaduan').trigger('reset');
                            $('#form-pengaduan').removeClass('was-validated')
                        })
                }
            })
        }

        $('#form-pengaduan').submit(function(event) {
            event.preventDefault()
            if (this.checkValidity()) {
                $insert(this)
            }
        })
    })
</script>
