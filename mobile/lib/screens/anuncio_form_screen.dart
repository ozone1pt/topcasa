import 'dart:io';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import 'package:material_ui/material_ui.dart';
import 'package:topcasa/core/api_exception.dart';
import 'package:topcasa/models/anuncio.dart';
import 'package:topcasa/models/catalogo.dart';
import 'package:topcasa/providers/providers.dart';

class AnuncioFormScreen extends ConsumerStatefulWidget {
  const AnuncioFormScreen({super.key, this.anuncioId});

  final int? anuncioId;

  @override
  ConsumerState<AnuncioFormScreen> createState() => _AnuncioFormScreenState();
}

class _AnuncioFormScreenState extends ConsumerState<AnuncioFormScreen> {
  final _form = GlobalKey<FormState>();
  final _titulo = TextEditingController();
  final _nome = TextEditingController();
  final _descricao = TextEditingController();
  final _contacto = TextEditingController();
  final _email = TextEditingController();
  final _youtube = TextEditingController();
  final _instagram = TextEditingController();
  final _picker = ImagePicker();
  int? _categoria;
  int? _distrito;
  var _certificacao = false;
  var _seguro = false;
  var _aCarregar = false;
  var _aEnviar = false;
  String? _erro;
  List<FotoGaleria> _existentes = [];
  final List<XFile> _novas = [];

  bool get _editar => widget.anuncioId != null;

  @override
  void initState() {
    super.initState();
    final user = ref.read(authControllerProvider).utilizador;
    _nome.text = user?.nome ?? '';
    _email.text = user?.email ?? '';
    _contacto.text = user?.telefone ?? '';
    if (_editar) {
      _carregar();
    }
  }

  Future<void> _carregar() async {
    setState(() => _aCarregar = true);
    try {
      final anuncio = await ref.read(apiClientProvider).anuncio(widget.anuncioId!);
      _titulo.text = anuncio.titulo;
      _nome.text = anuncio.nomeProfissional;
      _descricao.text = anuncio.descricao ?? '';
      _contacto.text = anuncio.contacto ?? '';
      _email.text = anuncio.emailContacto ?? _email.text;
      _youtube.text = anuncio.youtubeLink ?? '';
      _instagram.text = anuncio.instagramLink ?? '';
      _categoria = anuncio.categoriaId;
      _distrito = anuncio.distritoId;
      _certificacao = anuncio.certificacao;
      _seguro = anuncio.seguro;
      _existentes = List.of(anuncio.galeria);
    } catch (erro) {
      _erro = mensagemErro(erro);
    } finally {
      if (mounted) {
        setState(() => _aCarregar = false);
      }
    }
  }

  @override
  void dispose() {
    _titulo.dispose();
    _nome.dispose();
    _descricao.dispose();
    _contacto.dispose();
    _email.dispose();
    _youtube.dispose();
    _instagram.dispose();
    super.dispose();
  }

  Map<String, dynamic> _campos() {
    return {
      'titulo': _titulo.text.trim(),
      'minha_empresa': _nome.text.trim(),
      'categoria_id': _categoria,
      'distrito_id': _distrito,
      'descricao': _descricao.text.trim(),
      'contacto': _contacto.text.trim(),
      'email': _email.text.trim().toLowerCase(),
      'youtube_link': _youtube.text.trim().isEmpty ? null : _youtube.text.trim(),
      'instagram_link': _instagram.text.trim().isEmpty ? null : _instagram.text.trim(),
      'certificacao_profissional': _certificacao ? 1 : 0,
      'seguro_responsabilidade_civil': _seguro ? 1 : 0,
      'destacar': 0,
    };
  }

  Future<void> _escolher(ImageSource origem) async {
    if (origem == ImageSource.camera) {
      final foto = await _picker.pickImage(
        source: origem,
        imageQuality: 82,
        maxWidth: 1400,
        maxHeight: 1400,
      );
      if (foto != null) {
        setState(() => _novas.add(foto));
      }
      return;
    }
    final fotos = await _picker.pickMultiImage(imageQuality: 82, maxWidth: 1400, maxHeight: 1400);
    if (fotos.isNotEmpty) {
      setState(() => _novas.addAll(fotos));
    }
  }

  Future<void> _submeter() async {
    if (!_form.currentState!.validate() || _categoria == null || _distrito == null) {
      setState(() => _erro = 'Preenche a categoria e o distrito.');
      return;
    }
    setState(() {
      _aEnviar = true;
      _erro = null;
    });
    try {
      final fotos = [for (final foto in _novas) File(foto.path)];
      final api = ref.read(apiClientProvider);
      final anuncio = _editar
          ? await api.atualizarAnuncio(widget.anuncioId!, _campos(), fotos)
          : await api.criarAnuncio(_campos(), fotos);
      if (mounted) {
        context.go('/anuncios/${anuncio.id}');
      }
    } catch (erro) {
      if (mounted) {
        setState(() => _erro = mensagemErro(erro));
      }
    } finally {
      if (mounted) {
        setState(() => _aEnviar = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final categorias = ref.watch(categoriasProvider);
    final distritos = ref.watch(distritosProvider);

    return Scaffold(
      appBar: AppBar(title: Text(_editar ? 'Editar anúncio' : 'Publicar anúncio')),
      body: _aCarregar
          ? const Center(child: CircularProgressIndicator())
          : Form(
              key: _form,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  TextFormField(
                    controller: _titulo,
                    decoration: const InputDecoration(labelText: 'Título'),
                    validator: (valor) => (valor == null || valor.trim().isEmpty) ? 'O título é obrigatório.' : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _nome,
                    decoration: const InputDecoration(labelText: 'O teu nome ou empresa'),
                    validator: (valor) => (valor == null || valor.trim().isEmpty) ? 'O nome é obrigatório.' : null,
                  ),
                  const SizedBox(height: 12),
                  categorias.when(
                    data: (lista) => _seletor(
                      'Categoria',
                      [for (final item in lista) item.comoNome],
                      _categoria,
                      (valor) => setState(() => _categoria = valor),
                    ),
                    loading: () => const LinearProgressIndicator(),
                    error: (_, _) => const Text('Não foi possível carregar as categorias.'),
                  ),
                  const SizedBox(height: 12),
                  distritos.when(
                    data: (lista) => _seletor(
                      'Distrito',
                      [for (final item in lista) item.comoNome],
                      _distrito,
                      (valor) => setState(() => _distrito = valor),
                    ),
                    loading: () => const LinearProgressIndicator(),
                    error: (_, _) => const Text('Não foi possível carregar os distritos.'),
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _descricao,
                    minLines: 4,
                    maxLines: 8,
                    decoration: const InputDecoration(labelText: 'Descrição'),
                    validator: (valor) => (valor == null || valor.trim().isEmpty) ? 'A descrição é obrigatória.' : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _contacto,
                    keyboardType: TextInputType.phone,
                    decoration: const InputDecoration(labelText: 'Contacto (telefone)'),
                    validator: (valor) => (valor == null || valor.trim().isEmpty) ? 'O contacto é obrigatório.' : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _email,
                    keyboardType: TextInputType.emailAddress,
                    decoration: const InputDecoration(labelText: 'Email'),
                    validator: (valor) => (valor == null || !valor.contains('@')) ? 'Indica um email válido.' : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(controller: _youtube, decoration: const InputDecoration(labelText: 'Link do YouTube')),
                  const SizedBox(height: 12),
                  TextFormField(controller: _instagram, decoration: const InputDecoration(labelText: 'Instagram')),
                  SwitchListTile(
                    contentPadding: EdgeInsets.zero,
                    title: const Text('Certificação profissional'),
                    value: _certificacao,
                    onChanged: (valor) => setState(() => _certificacao = valor),
                  ),
                  SwitchListTile(
                    contentPadding: EdgeInsets.zero,
                    title: const Text('Seguro de responsabilidade civil'),
                    value: _seguro,
                    onChanged: (valor) => setState(() => _seguro = valor),
                  ),
                  const Text('Fotos (até 5, JPEG, PNG ou WebP)'),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      for (final foto in _existentes)
                        Stack(
                          children: [
                            Image.network(foto.url, width: 88, height: 88, fit: BoxFit.cover),
                            Positioned(
                              right: 0,
                              top: 0,
                              child: IconButton(
                                onPressed: () async {
                                  await ref.read(apiClientProvider).apagarImagem(foto.id);
                                  setState(() => _existentes.remove(foto));
                                },
                                icon: const Icon(Icons.close, color: Colors.white),
                              ),
                            ),
                          ],
                        ),
                      for (final foto in _novas)
                        Image.file(File(foto.path), width: 88, height: 88, fit: BoxFit.cover),
                    ],
                  ),
                  Row(
                    children: [
                      TextButton.icon(
                        onPressed: () => _escolher(ImageSource.gallery),
                        icon: const Icon(Icons.photo_library_outlined),
                        label: const Text('Galeria'),
                      ),
                      TextButton.icon(
                        onPressed: () => _escolher(ImageSource.camera),
                        icon: const Icon(Icons.photo_camera_outlined),
                        label: const Text('Câmara'),
                      ),
                    ],
                  ),
                  if (_editar && _existentes.length > 1)
                    TextButton(
                      onPressed: () async {
                        final ids = _existentes.map((foto) => foto.id).toList().reversed.toList();
                        await ref.read(apiClientProvider).ordenarImagens(ids);
                        setState(() => _existentes = _existentes.reversed.toList());
                      },
                      child: const Text('Inverter a ordem das fotos'),
                    ),
                  if (_erro != null) Text(_erro!, style: TextStyle(color: Theme.of(context).colorScheme.error)),
                  const SizedBox(height: 12),
                  FilledButton(
                    onPressed: _aEnviar ? null : _submeter,
                    child: Text(_aEnviar ? 'A enviar...' : (_editar ? 'Guardar' : 'Publicar')),
                  ),
                ],
              ),
            ),
    );
  }

  Widget _seletor(String rotulo, List<Nomeavel> opcoes, int? valor, ValueChanged<int?> aoMudar) {
    return DropdownButtonFormField<int>(
      initialValue: opcoes.any((item) => item.id == valor) ? valor : null,
      decoration: InputDecoration(labelText: rotulo),
      items: [for (final item in opcoes) DropdownMenuItem(value: item.id, child: Text(item.nome))],
      onChanged: aoMudar,
    );
  }
}

class Nomeavel {
  Nomeavel(this.id, this.nome);
  final int id;
  final String nome;
}

extension on Categoria {
  Nomeavel get comoNome => Nomeavel(id, nome);
}

extension on Distrito {
  Nomeavel get comoNome => Nomeavel(id, nome);
}
