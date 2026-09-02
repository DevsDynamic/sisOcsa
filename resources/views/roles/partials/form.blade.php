@php
    $moduleNames = ['moduleSystem'=>'Administración del sistema','moduleRetransmission'=>'Retransmisión e integraciones','modulePeople'=>'Personas y clientes','moduleUsers'=>'Usuarios y seguridad','moduleReports'=>'Reportes'];
    $subModuleNames = ['Configuration'=>'Configuración del sistema','IntegrationMonitor'=>'Monitor de integración','Osinergmin'=>'Osinergmin'];
@endphp
<div class="role-editor">
 <div class="form-group mb-4"><label for="name">Nombre del rol <span class="text-danger">*</span></label><input id="name" type="text" name="name" class="form-control" placeholder="Ej. Supervisor de integración" value="{{ old('name',$role->name??'') }}">@error('name')<div class="text-danger mt-1">{{$message}}</div>@enderror</div>
 <div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><div><h2 class="h4 mb-1">Permisos del rol</h2><p class="text-muted mb-0">Organizados por módulo y función.</p></div><span class="badge badge-light border px-3 py-2">Selecciona únicamente lo necesario</span></div>
 <div id="FormRole" class="permission-modules">
 @foreach($groupedPermissions as $moduleKey=>$subModules)
  @php($direct=$subModules->get('',collect())) @php($modulePermission=$direct->first(fn($p)=>str_contains($p->name,'.module'))) @php($moduleTitle=$moduleNames[$moduleKey]??\Illuminate\Support\Str::headline($moduleKey?:'Otros permisos'))
  <section class="permission-module"><header class="permission-module-header"><div><i class="fas fa-layer-group"></i><span><strong>{{$moduleTitle}}</strong><small>{{$subModules->flatten()->count()}} permisos disponibles</small></span></div><label class="permission-select-all mb-0"><input type="checkbox" class="module-checkbox" @if($modulePermission) name="permissions[]" value="{{$modulePermission->id}}" @endif @checked($modulePermission&&isset($role)&&$role->hasPermissionTo($modulePermission->name))> Seleccionar módulo</label></header>
   <div class="permission-submodules">
   @foreach($subModules as $subModuleKey=>$permissions)
    @php($subPermission=$permissions->first(fn($p)=>str_contains($p->name,'.submodule'))) @php($visible=$permissions->reject(fn($p)=>str_contains($p->name,'.module')||str_contains($p->name,'.submodule'))) @php($subTitle=$subModuleKey===''?'Permisos generales':($subModuleNames[$subModuleKey]??\Illuminate\Support\Str::headline($subModuleKey)))
    @if($visible->isNotEmpty()||$subPermission)<article class="permission-submodule"><div class="permission-submodule-header"><strong>{{$subTitle}}</strong><label class="mb-0"><input type="checkbox" class="submodule-checkbox" @if($subPermission) name="permissions[]" value="{{$subPermission->id}}" @endif @checked($subPermission&&isset($role)&&$role->hasPermissionTo($subPermission->name))> Todo</label></div><div class="permission-options">
     @foreach($visible as $permission)<label class="permission-option"><input type="checkbox" name="permissions[]" value="{{$permission->id}}" class="submodule-permission" @checked(isset($role)&&$role->hasPermissionTo($permission->name))><span>{{$permission->description?:\Illuminate\Support\Str::headline($permission->name)}}</span></label>@endforeach
    </div></article>@endif
   @endforeach
   </div>
  </section>
 @endforeach
 </div>
 @error('permissions')<div class="alert alert-danger mt-3 mb-0">{{$message}}</div>@enderror
</div>
