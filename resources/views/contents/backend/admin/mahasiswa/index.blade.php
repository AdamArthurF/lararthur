@extends('layouts.backend.app')

@section('breadcumb')
    <h3>@yield('title')</h3>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ route('frontend.home.index') }}">Home</a>
        </li>
        <li class="breadcrumb-item">Backend</li>
        <li class="breadcrumb-item">Admin</li>
        <li class="breadcrumb-item"><a href="{{ route('backend.admin.mahasiswa.index') }}">Mahasiswa</a></li>
    </ol>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="text-center">Kelola Data Mahasiswa</h5>
            <p class="text-muted text-center mb-0">Sistem Informasi {{ config('app.name') }}</p>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filter_tanggal">Tanggal</label>
                        <input autocomplete="off" type="text" id="filter_tanggal" name="filter_tanggal"
                            class="form-control datepicker" placeholder="Pilih tanggal">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filter_fakultas">Fakultas</label>
                        <select id="filter_fakultas" name="filter_fakultas" class="js-select2 form-control">
                            <option></option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filter_prodi">Prodi</label>
                        <select id="filter_prodi" name="filter_prodi" class="js-select2 form-control" disabled>
                            <option></option>
                        </select>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="height: 100vh;">
            <div id="map" style="width: 100%; height: 100%; z-index: 0;"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12 d-flex justify-content-end flex-row flex-wrap align-content-center">
                    <button type="button" class="btn btn-primary btn-pill mr-2 mt-2" id="tombol_export_word"><i
                            class="fa fa-file-word-o"></i> Export DOCX</button>
                    <button type="button" class="btn btn-danger btn-pill mr-2 mt-2" id="tombol_export_pdf"><i
                            class="fa fa-file-pdf-o"></i> Export PDF</button>
                    <button type="button" class="btn btn-success btn-pill mr-2 mt-2" id="tombol_export_excel"><i
                            class="fa fa-file-excel-o"></i> Export XLSX</button>
                    <button type="button" class="btn btn-info btn-pill mt-2" id="tombol_tambah"><i class="fa fa-plus"></i>
                        Tambah Data</button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 table-responsive">
                    <table id="table_data" class="table table-striped table-hover"></table>
                </div>
            </div>
        </div>
    </div>

    <!-- END Page Content -->

    <!-- Modal -->
    @include('contents.backend.admin.mahasiswa.modal.tambah')
    @include('contents.backend.admin.mahasiswa.modal.ubah')
@endsection

@push('scripts')
    <script>
        let datatable, id_data, get_data, csrf, status_crud = false,
            tambah_data, ubah_data, hapus_data,
            map, map_modal, marker_modal, legend;
        // Document ready
        $(() => {

            /**
             * Keperluan disable inspect element
             */
            // ================================================== //
            // Disable right click
            $(document).contextmenu(function(event) {
                event.preventDefault()
            })

            $(document).keydown(function(event) {
                // Disable F12
                if (event.keyCode == 123) return false;

                // Disable Ctrl + Shift + I
                if (event.ctrlKey && event.shiftKey && event.keyCode == 'I'.charCodeAt(0)) {
                    return false;
                }

                // Disable Ctrl + Shift + J
                if (event.ctrlKey && event.shiftKey && event.keyCode == 'J'.charCodeAt(0)) {
                    return false;
                }

                // Disable Ctrl + U
                if (event.ctrlKey && event.keyCode == 'U'.charCodeAt(0)) {
                    return false;
                }
            })

            /**
             * Keperluan DataTable, Datepicker, Summernote dan BsCustomFileInput
             */
            // ================================================== //
            datatable = $('#table_data').DataTable({
                serverSide: true,
                processing: true,
                destroy: true,
                responsive: true,
                ajax: {
                    url: "{{ route('backend.admin.mahasiswa.data') }}",
                    type: 'GET',
                    dataType: 'JSON',
                    data: {},
                    beforeSend: () => {
                        if (!status_crud) {
                            Swal.fire({
                                title: 'Loading...',
                                allowEscapeKey: false,
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            })
                        }
                    },
                    complete: () => {
                        if (!status_crud) {
                            Swal.close()
                        } else {
                            status_crud = false
                        }
                    }
                },
                columnDefs: [{
                        targets: [0, 1, 2, 3, 4, 5, 6, 7, 8], // Sesuaikan dengan jumlah kolom
                        className: 'text-center'
                    },
                    {
                        targets: [0, 7, 8],
                        searchable: false,
                        orderable: false,
                    }
                ],
                order: [
                    [8, 'desc']
                ],
                columns: [{ // 0
                        title: '#',
                        name: '#',
                        data: 'DT_RowIndex',
                    },
                    { // 1
                        title: 'Foto',
                        name: 'foto_thumb',
                        data: 'foto_thumb',
                        render: (foto_thumb) => {
                            return $('<img>', {
                                src: `storage/uploads/mahasiswa/${foto_thumb}`,
                                class: "img-thumnail",
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
                        name: 'nama',
                        data: 'nama',
                    },
                    { // 4
                        title: 'Program Studi',
                        name: 'nama_prodi',
                        data: 'nama_prodi',
                    },
                    { // 5
                        title: 'Fakultas',
                        name: 'nama_fakultas',
                        data: 'nama_fakultas',
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
                            let tombol_ubah = $('<button>', {
                                type: 'button',
                                class: 'btn btn-success tombol_ubah',
                                'data-id': id,
                                html: $('<i>', {
                                    class: 'fa fa-edit'
                                }).prop('outerHTML'),
                                title: 'Ubah Data'
                            })

                            let tombol_hapus = $('<button>', {
                                type: 'button',
                                class: 'btn btn-danger tombol_hapus',
                                'data-id': id,
                                html: $('<i>', {
                                    class: 'fa fa-trash'
                                }).prop('outerHTML'),
                                title: 'Hapus Data'
                            })

                            return $('<div>', {
                                role: 'group',
                                class: 'btn-group btn-group-sm',
                                html: [tombol_ubah, tombol_hapus]
                            }).prop('outerHTML')
                        }
                    }
                ],
            })

            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                endDate: 'now',
                clearBtn: true,
                todayBtn: 'linked',
                autoclose: true
            })

            bsCustomFileInput.init()
            // ================================================== //

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
                        response.data.map(item => {
                            myResults.push({
                                'id': item.id,
                                'text': item.nama
                            });
                        })
                        params.page = params.page || 1;

                        return {
                            results: myResults,
                            pagination: {
                                more: (params.page * 10) < response.total
                            }
                        };
                    }
                }
            }).on('select2:select', function(event) {
                $(`#filter_prodi`).prop('disabled', false)
                datatable.column('nama_fakultas:name')
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
                            response.data.map(item => {
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
                                    more: (params.page * 10) < response.total
                                }
                            };
                        }
                    }
                }).on('select2:select', function(event) {
                    datatable.column('nama_prodi:name')
                        .search(event.params.data.text)
                        .draw()
                })
            })

            // ================================================== //

            /**
             * Keperluan status_CRUD
             */
            // ================================================== //
            get_data = (element) => {
                Swal.fire({
                    title: 'Loading...',
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                })

                let row = datatable.row($(this).closest('tr')).data()

                $('#modal_ubah').modal('show');
                $('#form_ubah input#ubah_nim[name=nim]').val(row.nim);
                $('#form_ubah input#ubah_nama[name=nama]').val(row.nama);
                $('#form_ubah input#ubah_angkatan[name=angkatan]').val(row.angkatan);
                $('#form_ubah input#ubah_latitude[name=latitude]').val(row.latitude);
                $('#form_ubah input#ubah_longitude[name=longitude]').val(row.longitude);

                $('#form_ubah select#ubah_select_fakultas.select_fakultas')
                    .append(new Option(row.nama_fakultas, row.fakultas_id, true, true))
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
                    .append(new Option(row.nama_prodi, row.prodi_id, true, true))
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

                $('#form_ubah input#ubah_old_foto[name=old_foto]').val(row.foto)
                $('#form_ubah input#ubah_old_foto_thumb[name=old_foto_thumb]').val(row
                    .foto_thumb)
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

                Swal.close();
            }

            tambah_data = (form) => {
                $('#form_tambah button[type=submit]').hide();
                $('#form_tambah button.loader').show();
                Swal.fire({
                    title: 'Loading...',
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                })

                let formData = new FormData(form);
                axios.post("{{ route('backend.admin.mahasiswa.store') }}", formData)
                    .then(res => {
                        status_crud = true

                        // Upload File
                        let formUpload = new FormData();
                        formUpload.append('id', res.data.last_inserted_id)
                        formUpload.append('foto', $('input#tambah_foto[type=file]')[0].files[0])
                        axios.post("{{ route('backend.admin.mahasiswa.upload') }}", formUpload)
                            .then(res => {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: res.data.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                })
                            }).catch(err => {
                                console.error(err)
                            }).then(() => {
                                datatable.ajax.reload();
                            })
                    }).catch(err => {
                        console.error(err);
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
                    }).then(() => {
                        $('#form_tambah button[type=submit]').show();
                        $('#form_tambah button.loader').hide();
                        $('#form_tambah').trigger('reset');
                        $('#form_tambah select').val(null).trigger('change')
                        $('#form_tambah').removeClass('was-validated')
                        $('#modal_tambah').modal('hide');
                    })
            }

            ubah_data = async (form) => {
                Swal.fire({
                    title: 'Loading...',
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                })

                let formData = new FormData(form);
                formData.append('id', id_data);
                formData.append(
                    await csrf().then(csrf => csrf.token_name),
                    await csrf().then(csrf => csrf.hash)
                )

                fetch('update', {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    $('#form_ubah button[type=submit]').hide();
                    $('#form_ubah button.loader').show();
                    if (response.ok) return response.json()
                    throw new Error(response.statusText)
                }).then(response => {
                    status_crud = true
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    })
                    datatable.ajax.reload();
                }).catch(error => {
                    console.error(error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                        showConfirmButton: false,
                        timer: 1500
                    })
                }).finally(() => {
                    $('#form_ubah button[type=submit]').show();
                    $('#form_ubah button.loader').hide();
                    $('#form_ubah').trigger('reset');
                    $('#form_ubah select').val(null).trigger('change')
                    $('#form_ubah').removeClass('was-validated')
                    $('#modal_ubah').modal('hide');
                })
            }

            hapus_data = async (form) => {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Loading...',
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        })

                        let formData = new FormData();
                        formData.append('id', $(form).data('id'));
                        formData.append(
                            await csrf().then(csrf => csrf.token_name),
                            await csrf().then(csrf => csrf.hash)
                        )

                        fetch('delete', {
                            method: 'POST',
                            body: formData
                        }).then(response => {
                            if (response.ok) return response.json()
                            throw new Error(response.statusText)
                        }).then(response => {
                            status_crud = true
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            })
                            datatable.ajax.reload();
                        }).catch(error => {
                            console.error(error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong!',
                                showConfirmButton: false,
                                timer: 1500
                            })
                        })
                    }
                })
            }
            // ================================================== //

            /**
             * Keperluan event click tombol, reset, export, validasi dan submit form
             */
            // ================================================== //
            $('#tombol_tambah').click(event => {
                event.preventDefault();
                $('#modal_tambah').modal('show');
            });

            $('#table_data').on('click', '.tombol_ubah', function(event) {
                event.preventDefault()
                get_data(this);
            });

            $('#table_data').on('click', '.tombol_hapus', function(event) {
                event.preventDefault()
                hapus_data(this);
            });

            $('#form_tambah').submit(function(event) {
                event.preventDefault()
                if (this.checkValidity()) {
                    tambah_data(this);
                }
            });

            $('#form_ubah').submit(function(event) {
                event.preventDefault();
                if (this.checkValidity()) {
                    ubah_data(this);
                }
            });

            $('#modal_tambah').on('hide.bs.modal', () => {
                $('#form_tambah').removeClass('was-validated')
                $('#form_tambah').trigger('reset')
            })

            $('#modal_ubah').on('hide.bs.modal', () => {
                $('#form_ubah').removeClass('was-validated')
                $('#form_ubah').trigger('reset')
            })

            $('#tombol_export_excel').click(function() {
                location.replace('export_excel');
            })

            $('#tombol_export_word').click(function() {
                location.replace('export_word');
            })

            $('#tombol_export_pdf').click(function() {
                location.replace('export_pdf');
            })

            // ================================================== //

            /**
             * Keperluan input select2 didalam form
             */
            // ================================================== //
            const select2_in_form = (status) => {
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
                            response.data.map(item => {
                                myResults.push({
                                    'id': item.id,
                                    'text': item.nama
                                });
                            })
                            params.page = params.page || 1;

                            return {
                                results: myResults,
                                pagination: {
                                    more: (params.page * 10) < response.total
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
                                response.data.map(item => {
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
                                        more: (params.page * 10) < response.total
                                    }
                                };
                            }
                        }
                    })
                })
            }

            $('#modal_tambah').on('show.bs.modal', () => {
                select2_in_form('tambah')
            })

            $('#modal_ubah').on('show.bs.modal', () => {
                select2_in_form('ubah')
            })
            // ================================================== //

            /**
             * Keperluan WebGIS dengan Leaflet
             */
            // ================================================== //
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

            const map_inside_modal = (status) => {

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
                map_inside_modal('tambah')
            })

            $('#modal_ubah').on('show.bs.modal', () => {
                map_inside_modal('ubah')
            })

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
            $.getJSON("{{ route('backend.admin.mahasiswa.get_geojson') }}", response => {
                let geojson = L.geoJSON(response, {
                    onEachFeature: (feature, layer) => {
                        layer.on({
                            mouseover: (event) => {
                                let layer = event.target;
                                layer.setStyle({
                                    weight: 5,
                                    dashArray: '',
                                    fillOpacity: 0.7
                                });
                                if (!L.Browser.ie && !L.Browser.opera && !L.Browser
                                    .edge) {
                                    layer.bringToFront();
                                }
                            },
                            mouseout: (event) => {
                                geojson.resetStyle(event.target)
                            },
                            click: (event) => {
                                map.fitBounds(event.target.getBounds());
                            }
                        })
                    }
                }).addTo(map)
            })

            fetch("{{ route('backend.admin.mahasiswa.get_kecamatan') }}")
                .then(response => {
                    if (response.ok) return response.json()
                    throw new Error(response.statusText)
                })
                .then(response => {
                    response.data.map(item => {
                        L.marker([item.latitude, item.longitude])
                            .addTo(map)
                            .bindPopup(
                                new L.Popup({
                                    autoClose: false,
                                    closeOnClick: false
                                })
                                .setContent(`<b>${item.nama}</b>`)
                                .setLatLng([item.latitude, item.longitude])
                            ).openPopup();
                    })
                })

            fetch("{{ route('backend.admin.mahasiswa.get_latlng') }}")
                .then(response => {
                    if (response.ok) return response.json()
                    throw new Error(response.statusText)
                })
                .then(response => {
                    response.data.map(item => {
                        L.marker([item.latitude, item.longitude], {
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
                            .addTo(map)
                            .bindPopup(
                                new L.Popup({
                                    autoClose: false,
                                    closeOnClick: false
                                })
                                .setContent(`<b>${item.nama}</b>`)
                                .setLatLng([item.latitude, item.longitude])
                            ).openPopup();
                    })
                })
        })
    </script>
@endpush
