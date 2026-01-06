<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TopBannerSetting;
use App\Models\HomeBanner;
use App\Models\HomeSetting;
use App\Models\FooterSection;
use App\Models\SocialLink;
use App\Models\FeaturedCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class HomeCmsController extends Controller
{
    // ========== TOP BANNER SETTINGS ==========
    
    /**
     * Obtener configuración del banner superior
     */
    public function getTopBanner(): JsonResponse
    {
        $setting = TopBannerSetting::where('is_active', true)->first();
        
        if (!$setting) {
            // Crear configuración por defecto si no existe
            $setting = TopBannerSetting::create([
                'text' => 'ENVÍO GRATIS DESDE S/79',
                'background_color' => '#3B82F6',
                'text_color' => '#FFFFFF',
                'is_active' => true,
            ]);
        }
        
        return response()->json(['data' => $setting]);
    }
    
    /**
     * Actualizar configuración del banner superior
     */
    public function updateTopBanner(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:255',
            'background_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
            'is_active' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $setting = TopBannerSetting::where('is_active', true)->first();
        
        if (!$setting) {
            $setting = TopBannerSetting::create($request->all());
        } else {
            $setting->update($request->all());
        }
        
        return response()->json([
            'message' => 'Configuración del banner superior actualizada',
            'data' => $setting,
        ]);
    }
    
    // ========== HOME BANNERS ==========
    
    /**
     * Obtener todos los banners del home
     */
    public function getHomeBanners(): JsonResponse
    {
        $banners = HomeBanner::where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function ($banner) {
                // Convertir background_image_url a URL completa si es necesario
                if ($banner->background_image_url) {
                    $url = $banner->background_image_url;
                    // Si no es una URL completa, convertirla
                    if (!filter_var($url, FILTER_VALIDATE_URL)) {
                        if (str_starts_with($url, '/storage/')) {
                            $banner->background_image_url = asset($url);
                        } else {
                            $banner->background_image_url = asset('storage/' . $url);
                        }
                    }
                }
                return $banner;
            });
        
        return response()->json(['data' => $banners]);
    }
    
    /**
     * Subir imagen para banner
     */
    public function uploadBannerImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            
            // Obtener dimensiones de la imagen
            $imageInfo = getimagesize($image->getPathname());
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            
            // Dimensiones recomendadas: 1920x800px (ratio 2.4:1)
            $recommendedWidth = 1920;
            $recommendedHeight = 800;
            $tolerance = 0.2; // 20% de tolerancia
            
            $widthRatio = $width / $recommendedWidth;
            $heightRatio = $height / $recommendedHeight;
            
            $warnings = [];
            
            // Verificar si las dimensiones están dentro del rango recomendado
            if ($widthRatio < (1 - $tolerance) || $widthRatio > (1 + $tolerance) || 
                $heightRatio < (1 - $tolerance) || $heightRatio > (1 + $tolerance)) {
                $warnings[] = "Dimensiones recomendadas: {$recommendedWidth}x{$recommendedHeight}px. Tu imagen es {$width}x{$height}px.";
            }
            
            // Verificar ratio aproximado (2.4:1)
            $currentRatio = $width / $height;
            $recommendedRatio = $recommendedWidth / $recommendedHeight; // 2.4
            if (abs($currentRatio - $recommendedRatio) > 0.3) {
                $warnings[] = "Ratio recomendado: 2.4:1 (ancho:alto). Tu imagen tiene ratio: " . number_format($currentRatio, 2) . ":1";
            }
            
            $imagePath = $image->store('banners', 'public');
            // Generar URL completa
            $imageUrl = asset('storage/' . $imagePath);
            
            $response = [
                'message' => 'Imagen subida exitosamente',
                'data' => [
                    'url' => $imageUrl,
                    'path' => $imagePath,
                    'width' => $width,
                    'height' => $height,
                ],
            ];
            
            if (!empty($warnings)) {
                $response['warnings'] = $warnings;
            }
            
            return response()->json($response);
        }
        
        return response()->json([
            'message' => 'No se proporcionó ninguna imagen',
        ], 400);
    }
    
    /**
     * Crear un nuevo banner
     */
    public function createHomeBanner(Request $request): JsonResponse
    {
        // Calcular orden automáticamente si no se proporciona
        $order = $request->order;
        if ($order === null) {
            $maxOrder = HomeBanner::max('order') ?? 0;
            $order = $maxOrder + 1;
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:255',
            'button_link' => 'nullable|string|max:500',
            'background_image_url' => ['nullable', 'string', 'max:500', function ($attribute, $value, $fail) {
                if ($value && !filter_var($value, FILTER_VALIDATE_URL) && !str_starts_with($value, '/')) {
                    $fail('El campo :attribute debe ser una URL válida o una ruta relativa.');
                }
            }],
            'background_color' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $data = $request->all();
        $data['order'] = $order;
        
        // Convertir background_image_url si es una ruta relativa
        if (isset($data['background_image_url']) && $data['background_image_url']) {
            $url = $data['background_image_url'];
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                if (str_starts_with($url, '/storage/')) {
                    $data['background_image_url'] = asset($url);
                } else {
                    $data['background_image_url'] = asset('storage/' . $url);
                }
            }
        }
        
        $banner = HomeBanner::create($data);
        
        // Asegurar que la URL esté completa en la respuesta
        if ($banner->background_image_url && !filter_var($banner->background_image_url, FILTER_VALIDATE_URL)) {
            if (str_starts_with($banner->background_image_url, '/storage/')) {
                $banner->background_image_url = asset($banner->background_image_url);
            } else {
                $banner->background_image_url = asset('storage/' . $banner->background_image_url);
            }
        }
        
        return response()->json([
            'message' => 'Banner creado exitosamente',
            'data' => $banner,
        ], 201);
    }
    
    /**
     * Actualizar un banner
     */
    public function updateHomeBanner(Request $request, $id): JsonResponse
    {
        $banner = HomeBanner::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:255',
            'button_link' => 'nullable|string|max:500',
            'background_image_url' => ['nullable', 'string', 'max:500', function ($attribute, $value, $fail) {
                if ($value && !filter_var($value, FILTER_VALIDATE_URL) && !str_starts_with($value, '/')) {
                    $fail('El campo :attribute debe ser una URL válida o una ruta relativa.');
                }
            }],
            'background_color' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $data = $request->all();
        
        // Convertir background_image_url si es una ruta relativa
        if (isset($data['background_image_url']) && $data['background_image_url']) {
            $url = $data['background_image_url'];
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                if (str_starts_with($url, '/storage/')) {
                    $data['background_image_url'] = asset($url);
                } else {
                    $data['background_image_url'] = asset('storage/' . $url);
                }
            }
        }
        
        $banner->update($data);
        
        // Asegurar que la URL esté completa en la respuesta
        $updatedBanner = $banner->fresh();
        if ($updatedBanner->background_image_url && !filter_var($updatedBanner->background_image_url, FILTER_VALIDATE_URL)) {
            if (str_starts_with($updatedBanner->background_image_url, '/storage/')) {
                $updatedBanner->background_image_url = asset($updatedBanner->background_image_url);
            } else {
                $updatedBanner->background_image_url = asset('storage/' . $updatedBanner->background_image_url);
            }
        }
        
        return response()->json([
            'message' => 'Banner actualizado exitosamente',
            'data' => $updatedBanner,
        ]);
    }
    
    /**
     * Eliminar un banner
     */
    public function deleteHomeBanner($id): JsonResponse
    {
        $banner = HomeBanner::findOrFail($id);
        $banner->delete();
        
        return response()->json([
            'message' => 'Banner eliminado exitosamente',
        ]);
    }
    
    // ========== HOME SETTINGS ==========
    
    /**
     * Obtener todas las configuraciones del home
     */
    public function getHomeSettings(): JsonResponse
    {
        $settings = HomeSetting::all()->pluck('value', 'key');
        
        return response()->json(['data' => $settings]);
    }
    
    /**
     * Actualizar una configuración del home
     */
    public function updateHomeSetting(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:255',
            'value' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $setting = HomeSetting::updateOrCreate(
            ['key' => $request->key],
            ['value' => $request->value]
        );
        
        return response()->json([
            'message' => 'Configuración actualizada',
            'data' => $setting,
        ]);
    }
    
    // ========== FOOTER SECTIONS ==========
    
    /**
     * Obtener todas las secciones del footer
     */
    public function getFooterSections(): JsonResponse
    {
        $sections = FooterSection::orderBy('position')->get();
        
        return response()->json(['data' => $sections]);
    }
    
    /**
     * Actualizar una sección del footer
     */
    public function updateFooterSection(Request $request, $id): JsonResponse
    {
        $section = FooterSection::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'position' => 'nullable|integer|min:1|max:4',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'logo_url' => 'nullable|url|max:500',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'links' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $section->update($request->all());
        
        return response()->json([
            'message' => 'Sección del footer actualizada',
            'data' => $section->fresh(),
        ]);
    }
    
    /**
     * Crear una sección del footer
     */
    public function createFooterSection(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'position' => 'required|integer|min:1|max:4',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'logo_url' => 'nullable|url|max:500',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'links' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $section = FooterSection::create($request->all());
        
        return response()->json([
            'message' => 'Sección del footer creada',
            'data' => $section,
        ], 201);
    }
    
    /**
     * Eliminar una sección del footer
     */
    public function deleteFooterSection($id): JsonResponse
    {
        $section = FooterSection::findOrFail($id);
        $section->delete();
        
        return response()->json([
            'message' => 'Sección del footer eliminada',
        ]);
    }
    
    // ========== SOCIAL LINKS ==========
    
    /**
     * Obtener todos los links de redes sociales
     */
    public function getSocialLinks(): JsonResponse
    {
        $links = SocialLink::where('is_active', true)->get();
        
        return response()->json(['data' => $links]);
    }
    
    /**
     * Actualizar o crear un link de red social
     */
    public function updateSocialLink(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'platform' => 'required|string|in:facebook,instagram,tiktok,whatsapp',
            'url' => 'nullable|url|max:500',
            'is_active' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $link = SocialLink::updateOrCreate(
            ['platform' => $request->platform],
            $request->only(['url', 'is_active'])
        );
        
        return response()->json([
            'message' => 'Link de red social actualizado',
            'data' => $link,
        ]);
    }
    
    // ========== FEATURED CATEGORIES VISIBILITY ==========
    
    /**
     * Obtener todas las categorías destacadas con su estado de visibilidad
     */
    public function getFeaturedCategories(): JsonResponse
    {
        $categories = FeaturedCategory::with('category')
            ->orderBy('order')
            ->get();
        
        return response()->json(['data' => $categories]);
    }
    
    /**
     * Actualizar visibilidad de una categoría destacada
     */
    public function updateFeaturedCategoryVisibility(Request $request, $id): JsonResponse
    {
        $category = FeaturedCategory::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'is_active' => 'required|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $category->update(['is_active' => $request->is_active]);
        
        return response()->json([
            'message' => 'Visibilidad de categoría actualizada',
            'data' => $category->fresh(),
        ]);
    }
    
    // ========== BOTTOM BAR SETTINGS ==========
    
    /**
     * Obtener configuración de la barra inferior
     */
    public function getBottomBarSettings(): JsonResponse
    {
        $copyrightText = HomeSetting::where('key', 'bottom_bar_copyright_text')->first();
        $copyrightLink = HomeSetting::where('key', 'bottom_bar_copyright_link')->first();
        $backgroundColor = HomeSetting::where('key', 'bottom_bar_background_color')->first();
        
        return response()->json([
            'data' => [
                'copyright_text' => $copyrightText?->value ?? 'Copyright © {year} FATMAC | Todos los derechos | Elaborado por',
                'copyright_link' => $copyrightLink?->value ?? 'https://delacruzdev.tech/',
                'background_color' => $backgroundColor?->value ?? '#3B82F6',
            ],
        ]);
    }
    
    /**
     * Actualizar configuración de la barra inferior
     */
    public function updateBottomBarSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'copyright_text' => 'nullable|string|max:500',
            'copyright_link' => 'nullable|url|max:500',
            'background_color' => 'nullable|string|max:7',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        if ($request->has('copyright_text')) {
            HomeSetting::updateOrCreate(
                ['key' => 'bottom_bar_copyright_text'],
                ['value' => $request->copyright_text]
            );
        }
        
        if ($request->has('copyright_link')) {
            HomeSetting::updateOrCreate(
                ['key' => 'bottom_bar_copyright_link'],
                ['value' => $request->copyright_link]
            );
        }
        
        if ($request->has('background_color')) {
            HomeSetting::updateOrCreate(
                ['key' => 'bottom_bar_background_color'],
                ['value' => $request->background_color]
            );
        }
        
        return response()->json([
            'message' => 'Configuración de la barra inferior actualizada',
        ]);
    }
    
    // ========== NEWSLETTER SETTINGS ==========
    
    /**
     * Obtener texto de suscripción al newsletter
     */
    public function getNewsletterText(): JsonResponse
    {
        $setting = HomeSetting::where('key', 'newsletter_text')->first();
        
        return response()->json([
            'data' => [
                'text' => $setting?->value ?? 'Suscríbete y obtén el 10% de descuento en tu próxima compra.',
            ],
        ]);
    }
    
    /**
     * Actualizar texto de suscripción al newsletter
     */
    public function updateNewsletterText(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:500',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        HomeSetting::updateOrCreate(
            ['key' => 'newsletter_text'],
            ['value' => $request->text]
        );
        
        return response()->json([
            'message' => 'Texto del newsletter actualizado',
        ]);
    }
}
