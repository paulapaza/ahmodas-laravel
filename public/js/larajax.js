
/*** larajax datables ***/
class Larajax {
    constructor({
        data = false,
        idTable = "#table",
        mySrcData = false, /// datos para contruir la datable
        columns = false,
        actionsButtons = false,
        customTopButton = false,
        newRecordTopButton = true,
        alingCenter = false,
        linkShow = false,
        order = false,
        topButton = true,
        printTextHeader = false,
        printTextFooter = false,
        printSumaEnFooter = false,
        sumarcolumnafiltrada = false,

        mydataSrc = "", // funcion con datos antes de contruir la tabla
    }) {
        
        this.route = data.route;
        this.subject = data.subject;
        this.model = data.model;
        this.csrf = data.csrf;
        
        this.idTable = idTable;
        this.columns = columns;
        this.mySrcData = mySrcData;
        this.queryParams = data.queryParams;
        this.customTopButton = customTopButton;
        this.alingCenter = alingCenter;
        this.newRecordTopButton = newRecordTopButton;
        this.actionsButtons = actionsButtons;
        this.linkShow = linkShow;
        this.order = order;
        this.topButton = topButton;
        this.printTextHeader = printTextHeader;
        this.printTextFooter = printTextFooter;
        this.printSumaEnFooter = printSumaEnFooter;
        this.sumarcolumnafiltrada = sumarcolumnafiltrada;
        //pro datatables
        this.mydataSrc = mydataSrc;
        this.renderDataTable();
        return this.table;
    }
    renderDataTable() {
        const self = this;  // Guardar referencia de 'this' en una variable para accion footer
       
        this.table = $(this.idTable).DataTable({
            ///responsive: true,
            order: this.ordercolumn(),
            ajax: this.dataAjax(),
            columns: this.columnsTable(),
            columnDefs: this.renderColumsDef(),
            language: {
                lengthMenu: "Mostrar _MENU_ registros por página",
                zeroRecords: "No se encontraron resultados",
                info: "Mostrando la página _PAGE_ de _PAGES_",
                infoEmpty: "No hay registros disponibles",
                emptyTable: "No hay registros disponibles",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                search: "Buscar:",
                loadingRecords: "Cargando...",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior",
                },
                buttons: {
                    collection: "Colección",
                    colvis: "Visibilidad",
                    colvisRestore: "Restaurar visibilidad",
                    copy: "Copiar",
                    copyKeys:
                        "Presione ctrl o u2318 + C para copiar los datos de la tabla al portapapeles del sistema. <br /> <br /> Para cancelar, haga clic en este mensaje o presione escape.",
                    copySuccess: {
                        1: "Copiada 1 fila al portapapeles",
                        _: "Copiadas %d fila al portapapeles",
                    },
                    copyTitle: "Copiar al portapapeles",
                    csv: "CSV",
                    excel: "Excel",
                    pageLength: {
                        "-1": "Mostrar todas las filas",
                        _: "Mostrar %d filas",
                    },
                    pdf: "PDF",
                    print: "Imprimir",
                },
            },
            layout: {
                topStart: {
                    buttons: [this.topButtonDefault(this.subject)],
                },
            },
            data: this.datosdelatabla(),
            footerCallback: function (row, data, start, end, display) {
                self.sumarcolumna(this);
            }
             /* rowReorder: {
                dataSrc: 'id'
            } */
        });
    }
    sumarcolumna(tableInstance) {
        if (this.sumarcolumnafiltrada === false) {
            return null;
        } else {
            let idx_columna_sumar = this.sumarcolumnafiltrada.Column;
            let target = this.sumarcolumnafiltrada.target;
            
            var api = $(tableInstance).DataTable();
           
            var filteredTotal = api
                .column(idx_columna_sumar, { filter: 'applied' })
                .data()
                .reduce(function (a, b) {
                    return parseFloat(a) + parseFloat(b);
                }, 0);
            filteredTotal = filteredTotal.toFixed(2);
            $(target).html(filteredTotal);
        }
    }
    datosdelatabla() {
        if (this.mySrcData) {
            return this.mySrcData
        } else {
            return false
        }
    }
    ordercolumn() {
        if (this.order) {
            return this.order;
        } else {
            let defaultOrder = [[0, "desc"]];
            return defaultOrder;
        }
    }
    dataAjax() {
        if (this.route) {
            if (this.queryParams) {
                return {
                    url: this.route + this.queryParams,
                    dataSrc: this.mydataSrc,
                    data: this.data,
                    error: function (xhr, error, code) {
                        console.log(xhr, code);
                    },
                };
            } else {
                return {
                    url: this.route,
                    dataSrc: this.mydataSrc,
                    data: this.data,
                    error: function (xhr, error, code) {
                        //console.log(xhr, code);
                        alert(xhr.responseJSON.message);
                    },
                };
            }
        } else {
            return false;
        }
    }
    columnsTable() {
        if (!this.columns) {
            return false;
        }
        let columnas = this.columns;

        if (this.actionsButtons) {
            //columnas.push({ data: "id", title: "Accioness", className:"d-print-none" });
            columnas.push({ data: "id", title: "opciones" });
        }
        return columnas;
    }
    renderColumsDef() {
        let columnDefs = [
            {
                targets: [0, -1],
                className: "text-center",
            },
        ];
        if (this.linkShow) {
            columnDefs.push({
                targets: [this.linkShow.target],
                render: (data, type, row, meta) => {
                    return `<a href="${this.route}/${row["id"]}">${data}</a>`;
                },
            });
        }
        if (this.alingCenter) {
            columnDefs.push({
                targets: this.alingCenter,
                className: "text-center",
            });
        }
        if (this.actionsButtons) {
            columnDefs.push(this.renderActionsButtons(this.actionsButtons));
        }
        

        return columnDefs;
    }
    renderActionsButtons({
        view = false,
        editlink = false,
        print = false,
        edit = false,
        destroy = false,
        cancel = false,
        menuType = "default",
        customButton = false,
        customButtonLink = false,
    }) {
        return {
            targets: [-1],
            className: "d-print-none acciones",

            render: (data, type, row, meta) => {
                // crear variable denominacion y cargarle el valor de la columna 1, tener en cuenta que row tiene key con nombre y no sabemos los nombres
                // por eso tenemos que cambiar los key nombrados a key numerico
                // Función para evaluar condiciones específicas
                const shouldRenderButton = (buttonCondition) => {
                    if (typeof buttonCondition === "function") {
                        return buttonCondition(data, type, row, meta);
                    }
                    return true;
                };

                let fila = Object.values(row);

                let buttons = "";
                if (menuType == "default") {
                    buttons += view
                        ? `<a type="button" class="btn btn-xaccent btn-sm mr-2" href="${this.route}/${data}" >Ver</a>`
                        : "";
                    buttons += editlink
                        ? `<a type="button" class="btn btn-xaccent btn-sm mr-2" href="${this.route}/${data}/edit" >Editar</a>`
                        : "";
                    buttons += print
                        ? `<button type="button" class="btn btn-xaccent btn-sm btn-print mr-2" id=${data} subject="${fila[1]}">Imprimir</button>`
                        : "";
                    buttons += edit
                        ? `<button type="button" class="btn btn-xaccent btn-sm btn-edit mr-2" id=${data} subject="${fila[1]}" >Editar</button>`
                        : "";
                    //buttons += destroy ? `<button type="button" class="btn btn-primary btn-sm btn-destroy me-2" id=${data} ${this.model}="${fila[1]}" onclick="destroy_record('${fila[0]}','${fila[1]}')">Eliminar</button>` : '';
                    buttons += destroy
                        ? `<button type="button" class="btn btn-xaccent btn-sm btn-destroy mr-2" id=${data} subject="${fila[1]}" >Eliminar</button>`
                        : "";
                    buttons += cancel
                        ? `<button type="button" class="btn btn-primary btn-sm btn-cancel mr-2" route="${this.route}" modalId=${this.modalId} id=${data} subject="${fila[1]}" >Anular</button>`
                        : "";

                    if (customButton) {
                        customButton.forEach((element) => {
                            if (shouldRenderButton(element.condition)) {
                                buttons += `<button type="button" class="btn btn bg-xsecondary btn-sm btn-${element.action} mr-2" id="${data}" subject="${fila[1]}"><i class="${element.icon} mr-2"></i>${element.text}</button>`;
                            }
                        });
                    }
                    if (customButtonLink) {
                        customButtonLink.forEach((element) => {
                            if (shouldRenderButton(element.condition)) {
                                buttons += `<a href="${element.url}/${data}" type="button" class="btn btn btn-primary btn-sm  mr-2" id="${data}" subject="${fila[1]}"><i class="${element.icon} mr-2"></i>${element.text}</a>`;
                            }
                        });
                    }
                }

                if (menuType == "dropdown") {
                    let str_customButton = "";
                    if (customButtonLink) {
                        let icon = "";
                        let target = "";

                        customButtonLink.forEach((element) => {
                            if (shouldRenderButton(element.condition)) {
                                if (element.icon) {
                                    icon = `<i class="${element.icon} mr-2"></i>`;
                                }
                                if (element.target) {
                                    target = `target="${element.target}"`;
                                }
                                str_customButton += `<li><a href="${element.url}/${data}" ${target} class="dropdown-item" id="${data}" ${this.subject}="${fila[1]}">${icon}${element.text}</a><li>`;
                            }
                        });
                    }
                    if (customButton) {
                        customButton.forEach((element) => {
                            if (shouldRenderButton(element.condition)) {
                                str_customButton += `<li><button class="dropdown-item btn-${element.action} mr-2" id="${data}" subject="${fila[1]}"><i class="${element.icon} mr-2"></i>${element.text}</button></li>`;
                            }
                        });
                    }

                    let buttons = "";
                    buttons += view
                        ? `<li><a href="${this.route}/${data}" class="dropdown-item" id=${data}>Ver</a></li>`
                        : "";
                    buttons += editlink
                        ? `<li><a href="${this.route}/${data}/edit" class="dropdown-item" id=${data}>Editar</a></li>`
                        : "";
                    buttons += print
                        ? `<li><button class="dropdown-item btn-print" id=${data} ${this.subject}="${fila[1]}" >Imprimir</button></li>`
                        : "";
                    buttons += edit
                        ? `<li><button class="dropdown-item btn-edit" id=${data} ${this.subject}="${fila[1]}">Editar</button></li>`
                        : "";
                    buttons += destroy
                        ? `<li><button class="dropdown-item btn-destroy" id=${data} ${this.subject}="${fila[1]}">Eliminar</button></li>`
                        : "";
                    /// bootstrap 5 +
                    /* let dropdown = `
                   <div class="btn-group dropdown-center">
                        <button type="button" class="btn btn-sm btn-secondary ">Opciones</button>
                        <button type="button" class="btn btn-sm btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu">
                            ${buttons}
                            ${customButton}
                        </ul>
                   </div>`; */
                    let dropdown = `
                    <div class="btn-group dropleft">
                            <button type="button" class="btn btn-sm btn-secondary">Ociones</button>
                            <button type="button" class="btn btn-sm btn-secondary dropdown-toggle dropdown-icon" data-toggle="dropdown">
                            <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                            ${buttons}
                            ${str_customButton}
                            </ul>
                            </div>`;
                    return dropdown;
                }
                return buttons;
            },
        };
    }
    topButtonDefault() {
        if (this.topButton) {
            let custombuttons = [];
            if (this.customTopButton) {
                let topbuttons = [];
                this.customTopButton.forEach((element) => { /// boton personalizado para filtro
                    if (element.extend) {
                        topbuttons.push({
                            extend: element.extend,
                            text:
                                "<i class='" +
                                element.icon +
                                "'></i> " +
                                element.text,
                            className: element.class,
                            buttons: element.buttons,
                            autoClose: element.autoClose,
                        });
                    } else { /// boton personalizado normal
                        topbuttons.push({
                            text:
                                "<i class='" +
                                element.icon +
                                "'></i> " +
                                element.text,
                            className: element.class,
                            buttons: element.buttons,
                            action: (e, dt, node, config) => {
                                if (element.myfunction) {
                                    element.myfunction();
                                }
                                if (element.url) {
                                    if (element.target == "_blank") {
                                        window.open(element.url, "_blank");
                                    } else {
                                        window.location.href = element.url;
                                    }
                                }
                            },
                        });
                    }
                });

                custombuttons.push(topbuttons);
            }

            let newRecordTopButton = [
                {
                    text: "Nuevo " + this.subject,
                    className: "text-white bg-xsuccess text-xaccent text-bold",
                    action: (e, dt, node, config) => {
                        create_Record(this.route, this.subject);
                    },
                },
            ];

            let tooltButtons = [
                "pageLength",
                "copy",
                //"csv",
                "excel",
                //"print",
                {
                    extend: "print",
                    text: "Imprimir",
                    autoPrint: false,
                    exportOptions: {
                        columns: ":visible",
                    },
                    messageTop: this.printTextHeader,
                    messageBottom: this.printTextFooter,
                    customize: (win, object, table) => {
                        //console.log(object); imprime el objeto boton
                        if (this.printSumaEnFooter) {
                            if (this.printSumaEnFooter.agrupado) {
                                // Función para extraer valores únicos de una columna
                                function obtenerValoresUnicos(columnaIndex) {
                                    // Obtén todos los datos de la columna
                                    let datos = table
                                        .column(columnaIndex)
                                        .data()
                                        .toArray();

                                    // Usa un Set para eliminar duplicados
                                    let valoresUnicos = [...new Set(datos)];

                                    return valoresUnicos;
                                }
                                // Llama a la función para obtener valores únicos de la columna deseada
                                let valoresUnicos = obtenerValoresUnicos(
                                    this.printSumaEnFooter.agrupado.columnaIdx
                                );

                                // Iterar sobre cada agrupación
                                valoresUnicos.forEach((agrupacion) => {
                                    //this.printSumaEnFooter.agrupado.forEach((agrupacion) => {
                                    let suma = 0.0;
                                    let columna_a_sumar =
                                        this.printSumaEnFooter.columna; //'montototal'
                                    let columna_a_agrupar =
                                        this.printSumaEnFooter.agrupado.columna;
                                    let estado =
                                        this.printSumaEnFooter.condicion.estado

                                    // Itera sobre todas las filas de la tabla
                                    //return data == 1 ? 'Soles' : 'Dolares';
                                    table
                                        .rows()
                                        .eq(0)
                                        .each(function (index) {
                                            let row = table.row(index);
                                            let dat = row.data();

                                            // Verifica que el dato en la columna a sumar sea un número válido
                                            if (!isNaN(dat[columna_a_sumar]) && dat[columna_a_sumar] !== null && dat['estado'] == estado) {
                                                // Filtra por tipo de agrupación
                                                if (
                                                    dat[columna_a_agrupar] ===
                                                    agrupacion
                                                ) {
                                                    // 'tipo' debe ser el nombre de la columna que indica la moneda
                                                    suma += parseFloat(
                                                        dat[columna_a_sumar]
                                                    );
                                                }
                                            }
                                        });
                                    // Formatea la suma a dos decimales
                                    suma = Number(suma).toFixed(2);
                                    // Mostrar el total en el documento
                                    if (
                                        this.printSumaEnFooter.agrupado.valores
                                    ) {
                                        $(win.document.body).append(
                                            `<div class="mt-2 text-right font-weight-bold" style="font-size:18px">
                                                Total suma ${this.printSumaEnFooter.agrupado.valores[agrupacion]} ${suma}
                                            </div>`
                                        );
                                    } else {
                                        $(win.document.body).append(
                                            `<div class="mt-2 text-right font-weight-bold" style="font-size:18px">
                                            Total suma ${agrupacion} :  ${suma}
                                        </div>`
                                        );
                                    }
                                });
                            } else {
                                let suma = 0.0;
                                let columna_a_sumar =
                                    this.printSumaEnFooter.columna; //'montototal'
                                let estado =
                                    this.printSumaEnFooter.condition.estado

                                table
                                    .rows()
                                    .eq(0)
                                    .each(function (index) {
                                        let row = table.row(index);
                                        let dat = row.data();

                                        // Verifica que el dato en la columna a sumar sea un número válido
                                        if (!isNaN(dat[columna_a_sumar]) && dat[columna_a_sumar] !== null && dat['estado'] == estado) {

                                            suma += parseFloat(
                                                dat[columna_a_sumar]
                                            );

                                        }
                                    });
                                // Formatea la suma a dos decimales
                                suma = Number(suma).toFixed(2);

                                $(win.document.body).append(
                                    `<div class="mt-2 text-right font-weight-bold" style="font-size:18px">
                                        Total suma :  ${suma}
                                    </div>`
                                );
                            }
                        }
                    },
                },
                "colvis",
                //printButton,
                /*   {
                    extend: 'print',
                    text: 'custom Print current page',
                    autoPrint: false,
                    exportOptions: {
                        columns: ':visible',
                    },
                    customize: function (win) {
                        $(win.document.body).find('table').addClass('display').css('font-size', '9px');
                        $(win.document.body).find('tr:nth-child(odd) td').each(function(index){
                            $(this).css('background-color','#D0D0D0');
                        });
                        $(win.document.body).find('h1').css('text-align','center');
                        printTextHeader
                    }
                } */
            ];
            if (!this.newRecordTopButton) {
                return [...custombuttons, ...tooltButtons];
            } else {
                return [
                    ...custombuttons,
                    ...newRecordTopButton,
                    ...tooltButtons,
                ];
            }
        } else {
            return [];
        }
    }
    getTable() {
        return this.table;
    }
    getRoute() {
        return this.route;
    }
}
/*************************************************
 funciones generales
*************************************************/
function showConfirmSwal({
    accion = "sin accion",
    id = "",
    name = "",
    message = "",
    icon = "question",
    model = "none",
  }) {
    let title_accion = accion;
  
    if (accion == "store") {
      //message = `Se creará el ${entidad}: <b> ${name}</b>`;
      title_accion = "crear";
    }
    if (accion == "update") {
      message = `El ${model.nombre}: <b>${id}</b> se actualizará con los datos ingresados`;
      title_accion = "actualizar";
    }
    if (accion == "destroy") {
      message = `Esta acción no se puede deshacer, eliminará el registro ID: ${id} - <b>${model.nombre}: ${name}</b> y todos sus registros asociados`;
      title_accion = "eliminar";
    }
  
    return Swal.fire({
      title: `¿Desea ${title_accion} el ${model.nombre}?`,
      html: message,
      icon: icon,
      showCancelButton: true,
      customClass: {
        confirmButton: "btn btn-xsuccess mr-2",
        cancelButton: "btn btn-xsecondary",
      },
      buttonsStyling: false,
      confirmButtonText: "Si",
      focusCancel: true,
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        return true;
      } else {
        return null;
      }
    });
  }
function darkmode() {
    /// guardamos la variable darkMode en el localstorage para lograr la persistecia
    if ($("body").hasClass("dark-mode")) {
        localStorage.setItem("darkMode", "false");
        $("body").removeClass("dark-mode");
    } else {
        localStorage.setItem("darkMode", "true");
        $("body").addClass("dark-mode");
    }
}
function no_send_form(idform = "#form") {
    $(idform).submit(function (e) {
        e.preventDefault();
    });
}
function clean_form_input() {
   //$(':input:not([name="_token"],#id,#fecha_envio)').val("");
   $(':input:not([name="_token"], #id, #fecha_envio, select:has(option[selected]))').val("");
}
function load_Input_for_edit(object) {
    for (prop in object) {
        $("#" + prop).val(object[prop]);
    }
}
function activate_button_on_input_change() {
   
    $(":input").keyup(function (e) {
        if (
            event.keyCode != 37 &&
            event.keyCode != 38 &&
            event.keyCode != 39 &&
            event.keyCode != 40 &&
            this.value != ""
        ) {
            $(".btn-store").prop("disabled", false);
        }
       
    });
    //si cambia el contenido del div con atributo ckeditor
    if (typeof editor !== "undefined") {
        editor.model.document.on("change:data", () => {
            $(".btn-store").prop("disabled", false);
        });
    }
    //si cambia el contenido del input file
     $("input[type='file']").change(function () {
        $(".btn-store").prop("disabled", false);
    });
     //SI EL INPUT   tipo date cambia
    $(":input[type=date]").change(function () {
        //console.log("cambio el input date");
        $(".btn-store").prop("disabled", false);
    });
    //number
    $(":input[type=number]").change(function () {
        console.log("activar boton al cambiar input");
        //console.log("cambio el input number");
        $(".btn-store").prop("disabled", false);
    });
    $(":input[type=number]").keydown(function (e) {
        //tecla enter o numero
        //console.log("activar boton al cambiar input number");
        if (
            event.keyCode == 13 ||
            (event.keyCode >= 48 && event.keyCode <= 57) ||
            (event.keyCode >= 96 && event.keyCode <= 105)
        ) {
            $(".btn-store").prop("disabled", false);
        }
    });
    //si cambia el select
    
    $("#modal select").change(function () {
        $(".btn-store").prop("disabled", false);
    });
   //si presionamos backspace o suprimir en el input o
    $(":input").keydown(function (e) {
        if (
            event.keyCode == 8 ||
            event.keyCode == 46 ||
            event.keyCode == 13 ||
            event.keyCode == 32
        ) {
            $(".btn-store").prop("disabled", false);
        }
    }
    );
}
function show_validate_errors(errors) {
   
    clean_validate_errors();
    $(".content-errors").removeClass("d-none");
    $(".message_errors").html("Se encontraron los siguientes errores:");
    $.each(errors.responseJSON.errors, function (key, value) {
        $(".validate_errors").append("<li>" + value + "</li>");
    });
    if (errors.status == 403) {
      
        $(".validate_errors").html(errors.responseJSON.message);
    }
    return true;
}
function clean_validate_errors() {
    $(".validate_errors").html("");
    $(".content-errors").addClass("d-none");
    $(".message_errors").text("");
    $(".btn-save").prop("disabled", true);
}
function swal_response_errors(errors) {
    $listErrors = "";
    $.each(errors.responseJSON.errors, function (key, value) {
        $listErrors += "<li>" + value + "</li>";
    });
    Swal.fire({
        icon: "error",
        title: "Hubo un error",
        html: "<ul>" + $listErrors + "</ul>",
        showConfirmButton: true,
    });
}
function swal_message_response(respuesta) {

    const swal_message_response = Swal.mixin({
        customClass: {
          confirmButton: "btn btn-xsuccess",
          cancelButton: "btn btn-xdanger"
        },
        buttonsStyling: false
      });

    if (respuesta["success"] == true) {
        swal_message_response.fire({
            icon: "success",
            title: respuesta["title"],
            html: respuesta["message"],
            showConfirmButton: true,
        });
        $("#modal").modal("hide");
        return true;
        
    } else if (respuesta["success"] == false) {
        swal_message_response.fire({
            icon: "error",
            title: respuesta["title"],
            html: respuesta["message"],
            confirmButtonColor: "#FFDC7F",
            showConfirmButton: true,
        });

        return false;
    } else {
        swal_message_response.fire({
            icon: "error",
            title: "Hubo un error",
            html: respuesta,
            showConfirmButton: true,
        });

        return false;
    }
}
function isDoubleClicked(element) {
    if (element.data("isclicked")) return true;
    element.data("isclicked", true);
    setTimeout(function () {
        element.removeData("isclicked");
    }, 1500);
    return false;
}

/*************************************************
 funciones CRUD
*************************************************/
function create_Record(route, subject) {
    method = "POST";
    url = route;
    clean_validate_errors();
    clean_form_input();
    $("#modal").modal("show");
    $(".modal-title").text("Nuevo " + subject);
    $("#form").find(".btn-store").prop("disabled", true);
    if (typeof editor !== "undefined") {
        editor.setData("");
    }
    activate_button_on_input_change();
}
function edit_record(rowData, table, fila) {
    for (prop in rowData) {
        $("#" + prop).val(rowData[prop]);
    }
    $("#modal").modal("show");
    $(".modal-title").text(
        "Editar (" + fila.attr("id") + ") " + fila.attr("subject")
    );
    method = "PATCH"; // carga el metodo patch para editar en el store_record()
    $(".btn-store").attr("id", rowData.id);
    $(".content-errors").addClass("d-none");
    $("#form").find(".btn-store").prop("disabled", true);
    activate_button_on_input_change();
}
async function  store_record(dataAjax, formData, table) {
    let id = formData.get("id");
    let url = dataAjax.route;

    if (method == "PATCH") {
        url += "/" + id;
        formData.append("_method", "PATCH");
    }

    let ajax = await $.ajax({
        url: url,
        type: "POST",
        data: formData,
        processData: false, // No procesar los datos
        contentType: false, // No establecer el tipo de contenido
        success: function (respuesta) {
            return swal_message_response(respuesta) ? table.ajax.reload() : false;
        },
        error: function (error) {
            return show_validate_errors(error)? false : null;
        },
    });
    return ajax;
}
function store_record_serialize(id, dataAjax, serializeData, table) {

    if (method == "PUT") {
        url = dataAjax.route + "/" + id;
    } else if (method == "POST") {
        url = dataAjax.route;
    }

    $.ajax({
        url: url,
        type: method,
        data: serializeData,
        success: function (respuesta) {
            return swal_message_response(respuesta) ? table.ajax.reload() : false;
        },
        error: function (error) {
            show_validate_errors(error);
        },
    });

    /* $.ajax({
        cache: false,
        contentType: false,
        data: formData,
        dataType: "JSON",
        enctype: "multipart/form-data",
        processData: false,
        method: method,
        url: dataAjax.route + "/" + id,
        success: function (data) {
            console.log(data);
        },
    }); */

    /*  Swal.fire({
        title: accion  +' '+ dataAjax.subject,
        html: "¿esta seguro de "+ accion + ' el registro?',
        icon: "question",
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: "Guardar",
        cancelButtonText: "Cancelar",
        showLoaderOnConfirm: true,
        preConfirm: () => {
            
             $.ajax({
                url: dataAjax.route+"/"+id,
                type: method,
                data: formData,
                success: function (respuesta) {
                    swal_message_response(respuesta) ? table.ajax.reload() : null;
                },
                error: function (error) {
                    console.log(error);
                }
            });
        }, 
       

    });*/
}
function destroy_record(dataAjax, table, rowData) {
    let row = Object.values(rowData);

    Swal.fire({
        title: "Eliminar " + dataAjax.subject,
        text:
            "¿Está seguro de eliminar el " +
            dataAjax.subject +
            " : (" +
            rowData.id +
            ") " +
            row[1] +
            "?",
        icon: "question",
        showCancelButton: true,
        //confirmButtonColor: "#FFDC7F",
        //cancelButtonColor: "#78B7D0",
        confirmButtonText: "Si, eliminar",
        customClass: {
            confirmButton: "btn btn-xsuccess mr-2",
            cancelButton: "btn btn-xsecondary",
          },
        buttonsStyling: false,
        
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: dataAjax.route + "/" + rowData.id,
                type: "delete",
                dataType: "json",
                data: {
                    _token: dataAjax.csrf,
                },
                success: function (respuesta) {
                    swal_message_response(respuesta)
                        ? table.ajax.reload()
                        : null;
                },
                error: function (error) {
                    Swal.fire({
                        icon: "error",
                        title: "Hubo un error",
                        html: error,
                        showConfirmButton: true,
                    });
                },
            });
        }
    });
}
// anular record
$('#table').on("click", ".btn-cancel", function () {
    const rowData_id = $(this).attr('id');
    const route = $(this).attr('route');
    const modalId = $(this).attr('modalId');
    const subject = $(this).attr('subject');

    Swal.fire({
        title: "Anular",
        text:
            "¿Está seguro de anular a: " + subject + "?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si, anular",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: route + "/cancel/" + rowData_id,
                type: "PUT",
                dataType: "json",
                data: {
                    _token: token,
                },
                success: function (respuesta) {
                    swal_message_response(respuesta)
                        ? table.ajax.reload()
                        : null;
                },
                error: function (error) {
                    // llega aqui si mando error 400
                    Swal.fire({
                        icon: "error",
                        title: "Hubo un error",
                        html: error.responseJSON.message,
                        showConfirmButton: true,
                    });
                },
            });
        }
    });
});
