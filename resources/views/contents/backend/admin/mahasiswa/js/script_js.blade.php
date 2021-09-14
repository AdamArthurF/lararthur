<script>
    let datatable, id_data, $get, status_crud = false,
        $insert, $update, $delete, $import, map, map_modal,
        marker_modal, legend;

    // Document ready
    $(() => {
        /**
         * Keperluan WebGIS dengan Leaflet
         */
        // ================================================== //

        const initMap = () => {
            if (map) map.remove()
            map = L.map("map", {
                center: [-7.5828, 111.0444],
                zoom: 12,
                layers: [
                    /** OpenStreetMap Tile Layer */
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }),
                ]
            })
            map.scrollWheelZoom.disable();

            /** Legend */
            legend = L.control({
                position: "bottomleft"
            })

            legend.onAdd = (map) => {
                let div = L.DomUtil.create("div", "legend");
                div.innerHTML += "<h3><b>KABUPATEN KARANGANYAR</b></h3>";
                return div;
            }

            legend.addTo(map)
            /** GeoJSON Features */
            $.getJSON("{{ route('backend.admin.mahasiswa.get_geojson') }}",
                response => {
                    let geojson = L.geoJSON(response, {
                        onEachFeature: (feature, layer) => {
                            layer.on({
                                mouseover: (event) => {
                                    let layer = event
                                        .target;
                                    layer.setStyle({
                                        weight: 5,
                                        dashArray: '',
                                        fillOpacity: 0.7
                                    });
                                    if (!L.Browser.ie &&
                                        !L
                                        .Browser
                                        .opera &&
                                        !L.Browser
                                        .edge) {
                                        layer
                                            .bringToFront();
                                    }
                                },
                                mouseout: (event) => {
                                    geojson.resetStyle(event.target)
                                },
                                click: (event) => {
                                    map.fitBounds(event
                                        .target
                                        .getBounds()
                                    );
                                }
                            })
                        }
                    }).addTo(map)
                })

            axios.get("{{ route('backend.admin.mahasiswa.get_kecamatan') }}")
                .then(res => {
                    let results = res.data.data
                    results.map(item => {
                        if (item.latitude && item.longitude) {
                            L.marker([item.latitude, item.longitude])
                                .addTo(map)
                                .bindPopup(
                                    new L.Popup({
                                        autoClose: false,
                                        closeOnClick: false
                                    })
                                    .setContent(`<b>${item.nama}</b>`)
                                    .setLatLng([item.latitude, item
                                        .longitude
                                    ])
                                ).openPopup();
                        }
                    })
                })

            axios.get("{{ route('backend.admin.mahasiswa.get_latlng') }}")
                .then(res => {
                    let results = res.data.data
                    results.map(item => {
                        if (item.latitude && item.longitude) {
                            L.marker([item.latitude, item.longitude], {
                                    icon: L.icon({
                                        iconUrl: 'https://upload.wikimedia.org/wikipedia/commons/f/f2/678111-map-marker-512.png',
                                        iconSize: [40,
                                            40
                                        ], // size of the icon
                                        iconAnchor: [
                                            20, 40
                                        ], // point of the icon which will correspond to marker's location
                                        popupAnchor: [
                                            0, -30
                                        ] // point from which the popup should open relative to the iconAnchor
                                    })
                                })
                                .addTo(map)
                                .bindPopup(
                                    new L.Popup({
                                        autoClose: false,
                                        closeOnClick: false
                                    })
                                    .setContent(`<b>${item.nama}</b>`)
                                    .setLatLng([item.latitude, item
                                        .longitude
                                    ])
                                ).openPopup();
                        }
                    })
                })
        }

        const mapInModal = (status) => {

            if (map_modal) map_modal.remove()
            map_modal = L.map(`map-${status}`, {
                center: [-7.5828, 111.0444],
                zoom: 12,
                layers: [
                    /** OpenStreetMap Tile Layer */
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }),

                ]
            })

            setTimeout(() => {
                map_modal.invalidateSize()
            }, 500);

            map_modal.scrollWheelZoom.disable();
            map_modal.on('click', (event) => {
                if (marker_modal) map_modal.removeLayer(marker_modal)
                marker_modal = L.marker([event.latlng.lat, event.latlng
                    .lng
                ], { //-7.641355, 111.0377783
                    icon: L.icon({
                        iconUrl: 'https://upload.wikimedia.org/wikipedia/commons/f/f2/678111-map-marker-512.png',
                        iconSize: [40, 40], // size of the icon
                        iconAnchor: [
                            20, 40
                        ], // point of the icon which will correspond to marker's location
                        popupAnchor: [
                            0, -30
                        ] // point from which the popup should open relative to the iconAnchor
                    })
                })
                marker_modal.addTo(map_modal)
                marker_modal.bindPopup(`${event.latlng.lat}, ${event.latlng.lng}`).openPopup()

                $(`#${status}_latitude`).val(event.latlng.lat)
                $(`#${status}_longitude`).val(event.latlng.lng)
            })
        }

        $('#modal_tambah').on('show.bs.modal', () => {
            mapInModal('tambah')
        })

        $('#modal_ubah').on('show.bs.modal', () => {
            mapInModal('ubah')
        })

        initMap() // Init leaflet map

        /**
         * Keperluan DataTable, Datepicker, Summernote dan BsCustomFileInput
         */
        // ================================================== //
        datatable = $('#table_data').DataTable({
            serverSide: true,
            processing: true,
            destroy: true,
            dom: `<"dt-custom-filter mb-3 d-block">
                <"d-flex flex-row justify-content-end flex-wrap mb-2"B>
                <"d-flex flex-row justify-content-between"lf>
                rt
                <"d-flex flex-row justify-content-between"ip>`,
            buttons: {
                /** Tombol-tombol Export & Tambah Data */
                buttons: [{
                        className: 'btn btn-primary m-2',
                        text: $('<i>', {
                            class: 'fa fa-file-word-o'
                        }).prop('outerHTML') + ' Export DOCX', // Export DOCX
                        action: (e, dt, node, config) => {
                            location.replace(
                                "{{ route('backend.admin.mahasiswa.export_word') }}");
                        }
                    },
                    {
                        className: 'btn btn-danger m-2',
                        text: $('<i>', {
                            class: 'fa fa-file-pdf-o'
                        }).prop('outerHTML') + ' Export PDF', // Export PDF
                        action: (e, dt, node, config) => {
                            location.replace(
                                "{{ route('backend.admin.mahasiswa.export_pdf') }}");
                        }
                    },
                    {
                        className: 'btn btn-success m-2',
                        text: $('<i>', {
                            class: 'fa fa-file-excel-o'
                        }).prop('outerHTML') + ' Export XLSX', // Export XLSX
                        action: (e, dt, node, config) => {
                            location.replace(
                                "{{ route('backend.admin.mahasiswa.export_excel') }}");
                        }
                    },
                    {
                        className: 'btn btn-success m-2',
                        text: $('<i>', {
                            class: 'fa fa-upload'
                        }).prop('outerHTML') + ' Import XLSX', // Import XLSX
                        action: (e, dt, node, config) => {
                            $('#modal_import').modal('show')
                        }
                    },
                    {
                        className: 'btn btn-info m-2 text-white',
                        text: $('<i>', {
                            class: 'fa fa-plus'
                        }).prop('outerHTML') + ' Tambah Data', // Tambah Data
                        action: (e, dt, node, config) => {
                            $('#modal_tambah').modal('show');
                        }
                    },
                ],
                dom: {
                    button: {
                        className: 'btn'
                    },
                    buttonLiner: {
                        tag: null
                    }
                }
            },
            ajax: {
                url: "{{ route('backend.admin.mahasiswa.data') }}",
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
                    setTimeout(async () => {
                        await Swal.hideLoading()
                        await Swal.close()
                    },10);
                }
            },
            columnDefs: [{
                    targets: [0, 1, 2, 3, 4, 5, 6, 7, 8], // Sesuaikan dengan jumlah kolom
                    className: 'text-center'
                },
                {
                    targets: [0, 1, 7, 8],
                    searchable: false,
                    orderable: false,
                },
                {
                    targets: [9],
                    visible: false,
                    searchable: false,
                }
            ],
            order: [
                [9, 'DESC']
            ],
            columns: [{ // 0
                    title: '#',
                    name: '#',
                    data: 'DT_RowIndex',
                },
                { // 1
                    title: 'Foto',
                    name: 'foto',
                    data: 'foto',
                    render: (foto) => {
                        return $('<img>', {
                            src: (foto ? `{{ url('img/:foto') }}?w=100&h=200&fit=crop`
                                .replace(':foto', foto) : ''),
                            alt: 'Foto'
                        }).prop('outerHTML')
                    }
                },
                { // 2
                    title: 'NIM',
                    name: 'nim',
                    data: 'nim',
                },
                { // 3
                    title: 'Nama',
                    name: 'mahasiswa.nama', // Table.column -> Untuk menghindari order clause is ambiguous
                    data: 'nama',
                },
                { // 4
                    title: 'Program Studi',
                    name: 'prodi.nama', // Relationship.attribute
                    data: 'prodi.nama', // Relationship.attribute
                },
                { // 5
                    title: 'Fakultas',
                    name: 'fakultas.nama', // Relationship.attribute
                    data: 'fakultas.nama', // Relationship.attribute
                },
                { // 6
                    title: 'Angkatan',
                    name: 'angkatan',
                    data: 'angkatan',
                },
                { // 7
                    title: 'LatLng',
                    name: 'latlng',
                    data: (data) => {
                        return $('<span>', {
                                class: 'badge badge-primary',
                                html: data.latitude ? data.latitude : '-'
                            }).prop('outerHTML') + '<br>' +
                            $('<span>', {
                                class: 'badge badge-primary',
                                html: data.longitude ? data.longitude : '-'
                            }).prop('outerHTML')
                    }
                },
                { // 8
                    title: 'Aksi',
                    name: 'id',
                    data: 'id',
                    render: (id) => {
                        let btn_edit = $('<button>', {
                            type: 'button',
                            class: 'btn btn-success btn_edit',
                            'data-id': id,
                            html: $('<i>', {
                                class: 'fa fa-edit'
                            }).prop('outerHTML'),
                            title: 'Ubah Data',
                        })

                        let btn_delete = $('<button>', {
                            type: 'button',
                            class: 'btn btn-danger btn_delete',
                            'data-id': id,
                            html: $('<i>', {
                                class: 'fa fa-trash'
                            }).prop('outerHTML'),
                            title: 'Hapus Data'
                        })

                        return $('<div>', {
                            role: 'group',
                            class: 'btn-group btn-group-sm',
                            html: [btn_edit, btn_delete]
                        }).prop('outerHTML')
                    }
                },
                { // 9
                    title: 'Created At',
                    name: 'mahasiswa.created_at', // Table.column -> Untuk menghindari order clause is ambiguous
                    data: 'created_at',
                }
            ],
            initComplete: function(event) {
                $(this).on('click', '.btn_edit', function(event) {
                    event.preventDefault()
                    $get(this);
                });

                $(this).on('click', '.btn_delete', function(event) {
                    event.preventDefault()
                    $delete(this);
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

                    // Filter fakultas
                    let filter_fakultas = $('<div>', {
                        class: 'col-md-4',
                        html: [
                            $('<label>', {
                                for: 'filter_fakultas',
                                html: 'Fakultas',
                            }),
                            $('<select>', {
                                autocomplete: 'off',
                                type: 'text',
                                id: 'filter_fakultas',
                                name: 'filter_fakultas',
                                class: 'form-control js-select2',
                                html: $('<option>', {
                                    html: ''
                                })
                            })
                        ]
                    })

                    // Filter prodi
                    let filter_prodi = $('<div>', {
                        class: 'col-md-4',
                        html: [
                            $('<label>', {
                                for: 'filter_prodi',
                                html: 'Prodi',
                            }),
                            $('<select>', {
                                autocomplete: 'off',
                                type: 'text',
                                id: 'filter_prodi',
                                name: 'filter_prodi',
                                class: 'form-control js-select2',
                                disabled: true,
                                html: $('<option>', {
                                    html: ''
                                })
                            })
                        ]
                    })

                    return $('<div>', {
                        class: 'row',
                        html: [filter_tanggal, filter_fakultas, filter_prodi]
                    }).prop('outerHTML')
                })

                /**
                 * Keperluan filter menggunakan select2
                 */
                // ================================================== //
                $('#filter_fakultas').select2({
                    placeholder: 'Pilih Fakultas',
                    width: '100%',
                    ajax: {
                        url: "{{ route('backend.admin.mahasiswa.get_fakultas') }}",
                        dataType: 'JSON',
                        delay: 250,
                        data: function(params) {
                            return {
                                search: params.term, // search term
                                page: params.page || 1
                            };
                        },
                        processResults: function(response, params) {
                            let myResults = [];
                            let results = response.data.data
                            results.map(item => {
                                myResults.push({
                                    'id': item.id,
                                    'text': item.nama
                                });
                            })
                            params.page = params.page || 1;

                            return {
                                results: myResults,
                                pagination: {
                                    more: (params.page * 10) < response.data.total
                                }
                            };
                        }
                    }
                }).on('select2:select', function(event) {
                    $(`#filter_prodi`).prop('disabled', false)
                    datatable.column('fakultas.nama:name')
                        .search(event.params.data.text)
                        .draw()

                    $(`#filter_prodi`).select2({
                        placeholder: 'Pilih Program Studi',
                        width: '100%',
                        ajax: {
                            url: "{{ route('backend.admin.mahasiswa.get_prodi') }}",
                            dataType: 'JSON',
                            delay: 250,
                            data: function(params) {
                                return {
                                    search: params.term, // search term
                                    fakultas_id: event.params.data.id,
                                    page: params.page || 1
                                };
                            },
                            processResults: function(response, params) {
                                let myResults = [];
                                let results = response.data.data
                                results.map(item => {
                                    myResults.push({
                                        'id': item.id,
                                        'fakultas_id': event
                                            .params.data.id,
                                        'text': item.nama
                                    });
                                })
                                params.page = params.page || 1;

                                return {
                                    results: myResults,
                                    pagination: {
                                        more: (params.page * 10) < response.data
                                            .total
                                    }
                                };
                            }
                        }
                    }).on('select2:select', function(event) {
                        datatable.column('prodi.nama:name')
                            .search(event.params.data.text)
                            .draw()
                    })
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

        bsCustomFileInput.init()
        // ================================================== //


        /**
         * Keperluan CRUD
         */
        // ================================================== //

        // Get Data 
        $get = (element) => {
            let row = datatable.row($(element).closest('tr')).data()

            $('#modal_ubah').modal('show');
            $('#form_ubah input#ubah_id[name=id]').val(row.id)
            $('#form_ubah input#ubah_nim[name=nim]').val(row.nim);
            $('#form_ubah input#ubah_nama[name=nama]').val(row.nama);
            $('#form_ubah input#ubah_angkatan[name=angkatan]').val(row.angkatan);
            $('#form_ubah input#ubah_latitude[name=latitude]').val(row.latitude);
            $('#form_ubah input#ubah_longitude[name=longitude]').val(row.longitude);

            $('#form_ubah select#ubah_select_fakultas.select_fakultas')
                .append(new Option(row.fakultas.nama, row.fakultas_id, true, true))
                .trigger('change')
                .trigger({
                    type: 'select2:select',
                    params: {
                        data: {
                            id: row.fakultas_id,
                            fakultas_id: row.fakultas_id,
                            prodi_id: row.prodi_id
                        }
                    }
                })

            $('#form_ubah select#ubah_select_prodi.select_prodi')
                .append(new Option(row.prodi.nama, row.prodi_id, true, true))
                .trigger('change')
                .trigger({
                    type: 'select2:select',
                    params: {
                        data: {
                            fakultas_id: row.fakultas_id,
                            prodi_id: row.prodi_id
                        }
                    }
                })

            $('#form_ubah input#ubah_old_foto').val(row.foto)

            if (row.foto) {
                $('#form_ubah #lihat').removeClass('text-danger')
                $('#form_ubah #lihat').addClass('text-success')
                $('#form_ubah #lihat').html(
                    `<a href="uploads/mahasiswa/${row.foto}" target="_blank">Lihat file</a>`
                )
            } else {
                $('#form_ubah #lihat').addClass('text-danger')
                $('#form_ubah #lihat').removeClass('text-success')
                $('#form_ubah #lihat').html('File belum ada')
            }
        }

        // Insert Data
        $insert = (form) => {
            $('#form_tambah button[type=submit]').hide();
            $('#form_tambah button.loader').show();
            loading()

            status_crud = true
            let formData = new FormData(form);
            axios.post("{{ route('backend.admin.mahasiswa.insert') }}", formData)
                .then(res => {
                    initMap()

                    // Upload File
                    let formUpload = new FormData();
                    formUpload.append('id', res.data.last_inserted_id)
                    formUpload.append('foto', $('input#tambah_foto[type=file]')[0].files[0])
                    axios.post("{{ route('backend.admin.mahasiswa.upload') }}", formUpload)
                        .then(res => {
                            console.log(res)
                        }).catch(err => {
                            console.error(err)
                        }).then(() => {
                            datatable.ajax.reload();
                        })

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: res.data.message,
                        showConfirmButton: false,
                        timer: 1500
                    })
                    datatable.ajax.reload();
                }).catch(err => {
                    if (err.response.data.errors) {
                        let errors = '';
                        Object.entries(err.response.data.errors)
                            .forEach(function([key, value]) {
                                value.map(item => {
                                    errors +=
                                        `<i><i class="fa fa-angle-right"></i> ${value}</i> <br>`
                                })
                            })

                        Swal.fire({
                            icon: 'error',
                            title: err.response.data.message,
                            html: errors,
                        })
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            html: err.response.statusText,
                        })
                    }
                }).then(() => {
                    $('#form_tambah button[type=submit]').show();
                    $('#form_tambah button.loader').hide();
                    $('#form_tambah').trigger('reset');
                    $('#form_tambah select').val(null).trigger('change')
                    $('#form_tambah').removeClass('was-validated')
                    $('#modal_tambah').modal('hide');
                })
        }

        // Update Data
        $update = (form) => {
            loading()

            status_crud = true
            let formData = new FormData(form);
            formData.append('_method', 'PUT')
            axios.post("{{ route('backend.admin.mahasiswa.update') }}", formData)
                .then(res => {
                    initMap()

                    // Upload File
                    if ($('#ubah_foto[type=file]')[0].files[0]) { // Cek file
                        let formUpload = new FormData()
                        formUpload.append('id', res.data.last_updated_id)
                        formUpload.append('foto', $('input#ubah_foto[type=file]')[0]
                            .files[0])
                        axios.post("{{ route('backend.admin.mahasiswa.upload') }}",
                                formUpload)
                            .then(res => {
                                console.log(res)
                            }).catch(err => {
                                console.error(err)
                            }).then(() => {
                                datatable.ajax.reload()
                            })
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: res.data.message,
                        showConfirmButton: false,
                        timer: 1500
                    })
                    datatable.ajax.reload();
                }).catch(err => {
                    console.log(err)

                    if (err.response.data.errors) {
                        let errors = '';
                        Object.entries(err.response.data.errors)
                            .forEach(function([key, value]) {
                                value.map(item => {
                                    errors +=
                                        `<i><i class="fa fa-angle-right"></i> ${value}</i> <br>`
                                })
                            })
                        Swal.fire({
                            icon: 'error',
                            title: err.response.data.message,
                            html: errors,
                        })
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            html: err.response.statusText,
                        })
                    }

                }).then(() => {
                    $('#form_ubah button[type=submit]').show();
                    $('#form_ubah button.loader').hide();
                    $('#form_ubah').trigger('reset');
                    $('#form_ubah select').val(null).trigger('change')
                    $('#form_ubah').removeClass('was-validated')
                    $('#modal_ubah').modal('hide');
                })
        }

        // Delete Data
        $delete = (element) => {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    loading()

                    let formData = new FormData();
                    formData.append('id', $(element).data('id'));
                    formData.append('_method', 'DELETE')

                    axios.post("{{ route('backend.admin.mahasiswa.delete') }}", formData)
                        .then(res => {
                            status_crud = true
                            initMap()

                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: res.data.message,
                                showConfirmButton: false,
                                timer: 1500
                            })
                            datatable.ajax.reload()
                        }).catch(err => {
                            console.error(err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: err.response.statusText
                            })
                        })
                }
            })
        }

        $import = (form) => {
            Swal.fire({
                title: 'Are you sure?',
                text: "Document will be imported!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, import it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    loading()

                    let formData = new FormData(form);
                    axios.post("{{ route('backend.admin.mahasiswa.import_excel') }}", formData)
                        .then(res => {
                            status_crud = true
                            initMap()

                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: res.data.message,
                                showConfirmButton: false,
                                timer: 1500
                            })
                            datatable.ajax.reload();
                        }).catch(err => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: err.response.data.message,
                            })
                        }).then(() => {
                            $('#form_import button[type=submit]').show();
                            $('#form_import button.loader').hide();
                            $('#form_import').trigger('reset');
                            $('#form_import').removeClass('was-validated')
                            $('#modal_import').modal('hide');
                        })
                }
            })
        }
        // ================================================== //

        /**
         * Keperluan event click tombol, reset, validasi dan submit form
         */
        // ================================================== //
        $('#form_tambah').submit(function(event) {
            event.preventDefault()
            if (this.checkValidity()) {
                $insert(this);
            }
        });

        $('#form_ubah').submit(function(event) {
            event.preventDefault();
            if (this.checkValidity()) {
                $update(this);
            }
        });

        $('#form_import').submit(function(event) {
            event.preventDefault()
            if (this.checkValidity()) {
                $import(this)
            }
        })

        $('#form_import #downloadTemplateExcel').click(() => {
            location.replace("{{ route('backend.admin.mahasiswa.download_template_excel') }}")
        })

        $('#modal_tambah').on('hide.bs.modal', () => {
            $('#form_tambah').removeClass('was-validated')
            $('#form_tambah').trigger('reset')
        })

        $('#modal_ubah').on('hide.bs.modal', () => {
            $('#form_ubah').removeClass('was-validated')
            $('#form_ubah').trigger('reset')
        })

        // ================================================== //

        /**
         * Keperluan input select2 didalam form
         */
        // ================================================== //
        const select2InModal = (status) => {
            $(`#form_${status} select#${status}_select_fakultas.select_fakultas`).select2({
                placeholder: 'Pilih Fakultas',
                width: '100%',
                dropdownParent: $(`#modal_${status}`),
                ajax: {
                    url: "{{ route('backend.admin.mahasiswa.get_fakultas') }}",
                    dataType: 'JSON',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term, // search term
                            page: params.page || 1
                        };
                    },
                    processResults: function(response, params) {
                        let myResults = [];
                        let results = response.data.data
                        results.map(item => {
                            myResults.push({
                                'id': item.id,
                                'text': item.nama
                            });
                        })
                        params.page = params.page || 1;

                        return {
                            results: myResults,
                            pagination: {
                                more: (params.page * 10) < response.data.total
                            }
                        };
                    }
                }
            }).on('select2:select', function(event) {
                $(`#form_${status} select#${status}_select_prodi.select_prodi`)
                    .prop('disabled', false)

                $(`#form_${status} select#${status}_select_prodi.select_prodi`).select2({
                    placeholder: 'Pilih Program Studi',
                    width: '100%',
                    dropdownParent: $(`#modal_${status}`),
                    ajax: {
                        url: "{{ route('backend.admin.mahasiswa.get_prodi') }}",
                        dataType: 'JSON',
                        delay: 250,
                        data: function(params) {
                            return {
                                search: params.term, // search term
                                fakultas_id: event.params.data.id,
                                page: params.page || 1
                            };
                        },
                        processResults: function(response, params) {
                            let myResults = [];
                            let results = response.data.data
                            results.map(item => {
                                myResults.push({
                                    'id': item.id,
                                    'fakultas_id': event.params.data.id,
                                    'text': item.nama
                                });
                            })
                            params.page = params.page || 1;

                            return {
                                results: myResults,
                                pagination: {
                                    more: (params.page * 10) < response.data.total
                                }
                            };
                        }
                    }
                })
            })
        }

        $('#modal_tambah').on('show.bs.modal', () => {
            select2InModal('tambah')
        })

        $('#modal_ubah').on('show.bs.modal', () => {
            select2InModal('ubah')
        })
        // ================================================== //
    })
</script>
