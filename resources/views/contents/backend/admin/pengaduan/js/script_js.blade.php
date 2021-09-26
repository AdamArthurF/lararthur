<script>
    let datatable, status_crud = false,
        $get, $chat

    $(() => {
        /**
         * Keperluan DataTable, Datepicker, Summernote dan BsCustomFileInput
         */
        // ================================================== //
        datatable = $('#datatable').DataTable({
            serverSide: true,
            processing: true,
            destroy: true,
            // responsive: true,
            dom: `<"dt-custom-filter mb-3 d-block">
                <"d-flex flex-row justify-content-between"lf>
                rt
                <"d-flex flex-row justify-content-between"ip>`,
            ajax: {
                url: "{{ route('backend.admin.pengaduan.data') }}",
                type: 'GET',
                dataType: 'JSON',
                data: {},
                beforeSend: () => {
                    if (!status_crud) {
                        loading()
                    }
                },
                complete: () => {
                    if (status_crud) {
                        status_crud = false
                    }
                    setTimeout(() => {
                        Swal.hideLoading()
                        Swal.close()
                    }, 2000);
                }
            },
            columnDefs: [{
                    targets: [0, 1, 2, 3, 4, 5], // Sesuaikan dengan jumlah kolom
                    className: 'text-center'
                },
                {
                    targets: [0, 5],
                    searchable: false,
                    orderable: false,
                },
                {
                    targets: [6],
                    visible: false,
                    searchable: false,
                }
            ],
            order: [
                [6, 'desc']
            ],
            columns: [{ // 0
                    title: '#',
                    name: '#',
                    data: 'DT_RowIndex',
                },
                { // 1
                    title: 'Nama',
                    name: 'name',
                    data: 'name',
                },
                { // 2
                    title: 'Email',
                    name: 'email',
                    data: 'email',
                },
                { // 3
                    title: 'Phone',
                    name: 'phone',
                    data: 'phone',
                },
                { // 4
                    title: 'Pesan',
                    name: 'message',
                    data: 'message',
                },
                { // 5
                    title: 'Aksi',
                    name: 'id',
                    data: 'id',
                    render: (id) => {
                        let btn_detail = $('<button>', {
                            type: 'button',
                            class: 'btn btn-danger btn_detail',
                            'data-id': id,
                            html: $('<i>', {
                                class: 'fa fa-eye'
                            }).prop('outerHTML'),
                            title: 'Detail Pengaduan'
                        })

                        let btn_chat = $('<button>', {
                            type: 'button',
                            class: 'btn btn-success btn_chat',
                            'data-id': id,
                            html: $('<i>', {
                                class: 'fa fa-reply'
                            }).prop('outerHTML'),
                            title: 'Respon Pengaduan'
                        })

                        return $('<div>', {
                            role: 'group',
                            class: 'btn-group btn-group-sm',
                            html: [btn_detail, btn_chat]
                        }).prop('outerHTML')
                    }
                },
                { // 6
                    title: 'Created At',
                    name: 'created_at',
                    data: 'created_at',
                }
            ],
            initComplete: function(event) {
                $(this).on('click', '.btn_detail', function(event) {
                    event.preventDefault()
                    $get(this);
                });

                $(this).on('click', '.btn_chat', function(event) {
                    event.preventDefault()
                    $chat(this);
                });

                /** Elemen - elemen filter */
                $('.dt-custom-filter').html((index, currentContent) => {
                    // Filter tanggal
                    let filter_tanggal = $('<div>', {
                        class: 'col-md-4',
                        html: [
                            $('<label>', {
                                for: 'filter_tanggal',
                                html: 'Tanggal',
                            }),
                            $('<input>', {
                                autocomplete: 'off',
                                type: 'text',
                                id: 'filter_tanggal',
                                name: 'filter_tanggal',
                                class: 'form-control datepicker',
                                placeholder: 'Pilih Tanggal'
                            })
                        ]
                    })

                    return $('<div>', {
                        class: 'row',
                        html: [filter_tanggal]
                    }).prop('outerHTML')
                })

                // ================================================== //

                $('.datepicker').datepicker({
                    format: 'yyyy-mm-dd',
                    endDate: 'now',
                    clearBtn: true,
                    todayBtn: 'linked',
                    autoclose: true
                })
            },
        })

        datatable.on('draw.dt', function() {
            let PageInfo = datatable.page.info();
            datatable.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            endDate: 'now',
            clearBtn: true,
            todayBtn: 'linked',
            autoclose: true
        })

        bsCustomFileInput.init()

        // channel.bind('kirim-pengaduan-event', function(data) {
        //     status_crud = true
        //     datatable.ajax.reload()
        // });
        Echo.channel(`kirim-pengaduan-channel`)
            .listen('.kirim-pengaduan-event', (e) => {
                status_crud = true
                datatable.ajax.reload()
            });
        // ================================================== //

        /**
         * Keperluan CRUD
         */
        // ================================================== //
        $get = (element) => {
            let row = datatable.row($(element).closest('tr')).data();
            $('#modal_detail').modal('show');
            $('#form_detail input#detail_name[name=name]').val(row.name);
            $('#form_detail input#detail_email[name=email]').val(row.email);
            $('#form_detail input#detail_phone[name=phone]').val(row.phone);
            $('#form_detail input#detail_message[name=message]').val(row.message);
        }

        $chat = async (element) => {
            location.replace("{{ route('backend.admin.pengaduan.chat', ':id') }}".replace(':id', $(
                element).data('id')))
        }
    })
</script>
