<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <style>
        .text-center {
            text-align: center;
        }

        #map {
            width: 100%;
            height: 400px;
        }
    </style>
    <link rel='stylesheet' href='https://unpkg.com/leaflet@1.8.0/dist/leaflet.css' />
</head>

<body>
    <h1 class='text-center'>Laravel Leaflet Maps</h1>
    <div id='map'></div>
    <div>
        <button id="addCoordinateButton">Tambah Koordinat</button>
        <input type="text" id="newLatitude" placeholder="Latitude">
        <input type="text" id="newLongitude" placeholder="Longitude">
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src='https://unpkg.com/leaflet@1.8.0/dist/leaflet.js'></script>
    <script>
        let map, markers = [];

        function ajax() {
            $.ajax({
                type: 'GET',
                url: "{{ route('ajax') }}",
                success: function(response) {
                    for (let index = 0; index < response.length; index++) {
                        const data = response[index];
                        const marker = generateMarker(data, index);
                        marker.addTo(map).bindPopup(`<b>${data.lat},  ${data.lng}</b>`);
                        markers.push(marker);
                    }
                },
                error: function(error) {
                    handleAjaxError(error, 'Gagal mengambil data marker.');
                }
            });
        }
        // Fungsi untuk menangani klik pada tombol "Tambah Koordinat"
        function addCoordinateButton() {
            const newLatitude = parseFloat($('#newLatitude').val());
            const newLongitude = parseFloat($('#newLongitude').val());

            if (isNaN(newLatitude) || isNaN(newLongitude)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Input tidak valid',
                    text: 'Masukkan Latitude dan Longitude yang valid.'
                });
            } else {
                const newLatLng = {
                    lat: newLatitude,
                    lng: newLongitude
                };

                Swal.fire({
                    title: 'Tambah Koordinat Baru?',
                    text: `Latitude: ${newLatLng.lat}, Longitude: ${newLatLng.lng}`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Tambahkan!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        addCoordinateToServer(newLatLng.lat, newLatLng.lng);
                    }
                });
            }
        }

        // Menambahkan event click pada tombol "Tambah Koordinat"
        $('#addCoordinateButton').click(addCoordinateButton);

        function initMap() {
            map = L.map('map', {
                //peta pertama kali dimuat
                center: {
                    lat: -0.789275,
                    lng: 113.921327,
                },
                zoom: 4
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            map.on('click', mapClicked);

            ajax();
        }

        initMap();


        function generateMarker(data, index) {
            return L.marker(data, {
                    draggable: data.draggable
                })
                .on('click', (event) => markerClicked(event, index))
                .on('dragend', (event) => markerDragEnd(event, index));
        }

        function mapClicked(event) {
            const clickedLatLng = event.latlng;
            Swal.fire({
                title: 'Tambah Koordinat Baru?',
                text: `Latitude: ${clickedLatLng.lat}, Longitude: ${clickedLatLng.lng}`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Tambahkan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    addCoordinateToServer(clickedLatLng.lat, clickedLatLng.lng);
                }
            });
        }

        function addCoordinateToServer(latitude, longitude) {
            const data = {
                latitude: latitude,
                longitude: longitude
            };

            ajax();
            clearMarkers();

            $.ajax({
                type: 'POST',
                url: '{{ route('addCoordinate') }}',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: data,
                success: function(response) {
                    handleAjaxSuccess(response.message, 'Koordinat berhasil ditambahkan.');

                    // Setelah berhasil menambahkan, arahkan peta ke koordinat baru
                    newMarker = generateMarker({
                        lat: latitude,
                        lng: longitude
                    }, markers.length);
                    newMarker.addTo(map).bindPopup(`<b>${latitude}, ${longitude}</b>`);
                    markers.push(newMarker);

                    map.panTo([latitude, longitude]); // Pindahkan peta ke koordinat baru
                },
                error: function(error) {
                    handleAjaxError(error, 'Koordinat tidak dapat ditambahkan.');
                    ajax();
                }
            });
        }


        function clearMarkers() {
            markers.forEach(marker => marker.remove());
            markers = [];
        }

        function markerClicked(event, index) {
            console.log(event.latlng.lat, event.latlng.lng);
        }

        function markerDragEnd(event, index) {
            const updatedLatLng = event.target.getLatLng();
            Swal.fire({
                title: 'Update Koordinat?',
                text: `Latitude: ${updatedLatLng.lat}, Longitude: ${updatedLatLng.lng}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateMarkerCoordinates(index, updatedLatLng.lat, updatedLatLng.lng);
                } else {
                    // Restore marker to previous position
                    $.ajax({
                        type: 'GET',
                        url: '{{ route('find') }}',
                        data: {
                            index: index
                        },
                        success: function(response) {
                            const initialPosition = response;
                            if (initialPosition) {
                                const marker = markers[index];
                                marker.setLatLng([initialPosition.latitude, initialPosition.longitude]);
                            }
                        },
                        error: function(error) {
                            handleAjaxError(error);
                        }
                    });
                }
            });
        }

        function updateMarkerCoordinates(index, latitude, longitude) {
            $.ajax({
                type: 'GET',
                url: '{{ route('find') }}',
                data: {
                    index: index
                },
                success: function(response) {
                    const id = response.id;
                    const data = {
                        id: id,
                        latitude: latitude,
                        longitude: longitude
                    };

                    $.ajax({
                        type: 'POST',
                        url: '{{ route('updateCoordinates') }}',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: data,
                        success: function(response) {
                            handleAjaxSuccess(response.message, 'Coordinate berhasil diubah.');
                        },
                        error: function(error) {
                            handleAjaxError(error);
                        }
                    });
                },
                error: function(error) {
                    handleAjaxError(error);
                }
            });
        }

        function handleAjaxSuccess(message, title = 'Success') {
            console.log(message);
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: title,
                showConfirmButton: false,
                timer: 1500
            });
        }

        function handleAjaxError(error, title = 'Terjadi kesalahan') {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: title,
                text: 'Gagal melakukan operasi.'
            });
        }
    </script>
</body>

</html>
