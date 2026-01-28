<?php

namespace App\Forms\Components;

use App\Enums\MediaTypeEnum;
use App\Filament\Resources\MediaHlsResource;
use App\Filament\Resources\MediaHtmlResource;
use App\Filament\Resources\MediaImageResource;
use App\Filament\Resources\MediaLiveUrlResource;
use App\Filament\Resources\MediaQrCodeResource;
use App\Filament\Resources\MediaVideoResource;
use App\Models\MediaHls;
use App\Models\MediaHtml;
use App\Models\MediaImage;
use App\Models\MediaLiveUrl;
use App\Models\MediaQrcode;
use App\Models\MediaVideo;
use Filament\Forms;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;

class SelectMedia extends MorphToSelect
{
    protected string $view = 'forms.components.select-media';
    
    private function getHelperText($get, $typeColumn)
    {
        $helper = '';

        switch ($get($typeColumn)) {
            case MediaTypeEnum::IMAGE->value:
                $helper = new HtmlString('<a href="' . MediaImageResource::getUrl('index') . '" style="color:blue;" target="_blank">Show all images</a>');
                break;
            case MediaTypeEnum::VIDEO->value:
                $helper = new HtmlString('<a href="' . MediaVideoResource::getUrl('index') . '" style="color:blue;" target="_blank">Show all videos</a>');
                break;
            case MediaTypeEnum::HTML->value:
                $helper = new HtmlString('<a href="' . MediaHtmlResource::getUrl('index') . '" style="color:blue;" target="_blank">Show all html</a>');
                break;
            case MediaTypeEnum::HLS->value:
                $helper = new HtmlString('<a href="' . MediaHlsResource::getUrl('index') . '" style="color:blue;" target="_blank">Show all hls</a>');
                break;
            case MediaTypeEnum::LIVE_URL->value:
                $helper = new HtmlString('<a href="' . MediaLiveUrlResource::getUrl('index') . '" style="color:blue;" target="_blank">Show all live url</a>');
                break;

            default:
                break;
        }

        return $helper;
    }

    public function getChildComponents(): array
    {
        $relationship = $this->getRelationship();
        $typeColumn = $relationship->getMorphType();
        $keyColumn = $relationship->getForeignKeyName();

        $types = $this->getTypes();
        $isRequired = $this->isRequired();

        /** @var ?Type $selectedType */
        $selectedType = $types[$this->evaluate(fn(Get $get): ?string => $get($typeColumn))] ?? null;

        $whitelists = [
            MediaTypeEnum::IMAGE->value,
            MediaTypeEnum::VIDEO->value,
            MediaTypeEnum::HTML->value,
            MediaTypeEnum::QR_CODE->value,
            MediaTypeEnum::HLS->value,
            MediaTypeEnum::LIVE_URL->value,
            MediaTypeEnum::SLIDER->value,
        ];

        return [
            Forms\Components\Select::make($typeColumn)
                ->label($this->getLabel())
                ->hiddenLabel()
                ->options(array_map(
                    fn(Type $type): string => $type->getLabel(),
                    $types,
                ))
                ->helperText(fn(Forms\Get $get) => $this->getHelperText($get, $typeColumn))
                ->required($isRequired)
                ->live()
                ->afterStateUpdated(fn(Set $set) => $set($keyColumn, null))
                ->suffixActions([
                    Forms\Components\Actions\Action::make('default')
                        ->icon('heroicon-m-folder-open')
                        ->visible(fn(Forms\Get $get) => !in_array($get($typeColumn), $whitelists))
                        ->action(function () {
                            Notification::make()
                                ->warning()
                                ->title('Information')
                                ->body('Please select media')
                                ->send();
                        }),
                    Forms\Components\Actions\Action::make('addNewImage')
                        ->icon('heroicon-m-photo')
                        ->visible(fn(Forms\Get $get) => $get($typeColumn) == MediaTypeEnum::IMAGE->value)
                        ->form(MediaImageResource::formSchema())
                        ->action(function (Forms\Get $get, Forms\Set $set, MediaImage $image, $data) use ($typeColumn, $keyColumn) {
                            $record = $image->create($data);
                            $set($keyColumn, $record->id);
                        }),
                    Forms\Components\Actions\Action::make('addNewVideo')
                        ->icon('heroicon-m-video-camera')
                        ->visible(fn(Forms\Get $get) => $get($typeColumn) == MediaTypeEnum::VIDEO->value)
                        ->form(MediaVideoResource::formSchema())
                        ->action(function (Forms\Get $get, Forms\Set $set, MediaVideo $video, $data) use ($typeColumn, $keyColumn) {
                            $record = $video->create($data);
                            $set($keyColumn, $record->id);
                        }),
                    Forms\Components\Actions\Action::make('addNewHtml')
                        ->icon('heroicon-m-code-bracket')
                        ->visible(fn(Forms\Get $get) => $get($typeColumn) == MediaTypeEnum::HTML->value)
                        ->form(MediaHtmlResource::formSchema())
                        ->action(function (Forms\Get $get, Forms\Set $set, MediaHtml $html, $data) use ($typeColumn, $keyColumn) {
                            $record = $html->create($data);
                            $set($keyColumn, $record->id);
                        }),
                    Forms\Components\Actions\Action::make('addNewQrCode')
                        ->icon('heroicon-m-qr-code')
                        ->visible(fn(Forms\Get $get) => $get($typeColumn) == MediaTypeEnum::QR_CODE->value)
                        ->form(MediaQrCodeResource::formSchema())
                        ->action(function (Forms\Get $get, Forms\Set $set, MediaQrCode $qrcode, $data) use ($typeColumn, $keyColumn) {
                            $record = $qrcode->create($data);
                            $set($keyColumn, $record->id);
                        }),
                    Forms\Components\Actions\Action::make('addNewHls')
                        ->icon('heroicon-m-tv')
                        ->visible(fn(Forms\Get $get) => $get($typeColumn) == MediaTypeEnum::HLS->value)
                        ->form(MediaHlsResource::formSchema())
                        ->action(function (Forms\Get $get, Forms\Set $set, MediaHls $hls, $data) use ($typeColumn, $keyColumn) {
                            $record = $hls->create($data);
                            $set($keyColumn, $record->id);
                        }),
                    Forms\Components\Actions\Action::make('addNewLiveUrl')
                        ->icon('heroicon-m-globe-asia-australia')
                        ->visible(fn(Forms\Get $get) => $get($typeColumn) == MediaTypeEnum::LIVE_URL->value)
                        ->form(MediaLiveUrlResource::formSchema())
                        ->action(function (Forms\Get $get, Forms\Set $set, MediaLiveUrl $live_url, $data) use ($typeColumn, $keyColumn) {
                            $record = $live_url->create($data);
                            $set($keyColumn, $record->id);
                        }),
                    Forms\Components\Actions\Action::make('addNewSlider')
                        ->icon('heroicon-m-view-columns')
                        ->visible(fn(Forms\Get $get) => $get($typeColumn) == MediaTypeEnum::SLIDER->value)
                        ->url(route('filament.back-office.resources.media-sliders.index'), shouldOpenInNewTab: true),
                ]),
            Forms\Components\Select::make($keyColumn)
                ->label($selectedType?->getLabel())
                ->hiddenLabel()
                ->options($selectedType?->getOptionsUsing)
                ->getSearchResultsUsing($selectedType?->getSearchResultsUsing)
                ->getOptionLabelUsing($selectedType?->getOptionLabelUsing)
                ->required($isRequired)
                ->hidden(!$selectedType)
                ->searchable($this->isSearchable())
                ->searchDebounce($this->getSearchDebounce())
                ->searchPrompt($this->getSearchPrompt())
                ->searchingMessage($this->getSearchingMessage())
                ->noSearchResultsMessage($this->getNoSearchResultsMessage())
                ->loadingMessage($this->getLoadingMessage())
                ->allowHtml($this->isHtmlAllowed())
                ->optionsLimit($this->getOptionsLimit())
                ->preload($this->isPreloaded()),
        ];
    }
}
