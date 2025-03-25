<!-- Modal de Visualización de usuario -->
<div class="modal fade" id="modal-show-unit" tabindex="-1" aria-labelledby="showTypeCustomerLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">VER RETRANSMISIONES A OSINERGMIN DE LA UNIDAD: <strong><span class="plate"></span></strong></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
          
            <form id="formDispatch" name="formDispatch">
                <input type="hidden" id="hiddenIDdispatch" name="hiddenID" class="form-control">
                <div class="modal-header">
                    A continuación se muestra las retransmisiones a Osinergmin de los últimos 30 días calendario.
                </div>
                <div class="modal-body">                    
                    {{-- Seccion detalle de retransmisiones --}}
                    <div class="border rounded card-body border-secondary" id="vertabla">
                        <div class="card-body">
                            <div class="form-row">  
                                <div class="table-responsive">
                                    <table id="detalles" class="table table-striped align-middle table-centered" style="width:100%">
                                        <thead class="bg-primary">
                                            <tr class="bg-black">
                                                <th scope="col" colspan="8" class="text-center border">Datos OCSA</th>
                                                <th scope="col" colspan="4" class="text-center border">Respuesta Osinergmin</th>
                                            </tr>
                                            <tr>
                                                <th scope="col" class="border">N°</th>
                                                <th scope="col" class="border">Cod.</th>
                                                <th scope="col" class="border">Evento</th>
                                                <th scope="col" class="border">Velocidad</th>
                                                <th scope="col" class="border">Latitud</th>
                                                <th scope="col" class="border">Longitud</th>
                                                <th scope="col" class="border">Fecha de envío</th>
                                                <th scope="col" class="border">Kilometraje</th>
                                                <th scope="col" class="border">Fecha de respuesta</th>
                                                <th scope="col" class="border">Mensaje</th>
                                                <th scope="col" class="border">Sugerencia</th>
                                                <th scope="col" class="border">Estado</th>
                                            </tr>
                                        </thead>                                        
                                        <tbody>
                                            <!-- FILAS GENERADAS DINÁMICAMENTE POR JAVASCRIPT -->
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr>
                                                <th scope="col" class="border">N°</th>
                                                <th scope="col" class="border">Cod.</th>
                                                <th scope="col" class="border">Evento</th>
                                                <th scope="col" class="border">Velocidad</th>
                                                <th scope="col" class="border">Latitud</th>
                                                <th scope="col" class="border">Longitud</th>
                                                <th scope="col" class="border">Fecha de envío</th>
                                                <th scope="col" class="border">Kilometraje</th>
                                                <th scope="col" class="border">Fecha de respuesta</th>
                                                <th scope="col" class="border">Mensaje</th>
                                                <th scope="col" class="border">Sugerencia</th>
                                                <th scope="col" class="border">Estado</th>
                                            </tr>
                                        </tfoot>
                                    </table>                              
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times-circle"></i> Cerrar
                    </button>
                    {{-- <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Confirmar
                    </button> --}}
                </div>
            </form>
        </div>
    </div>
</div>
