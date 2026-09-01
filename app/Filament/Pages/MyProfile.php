<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\UserProfile;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MyProfile extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationLabel = 'Profil Saya';

    protected static ?int $navigationSort = 999;

    protected static ?string $title = 'Profil Pengguna Mandiri';

    protected static ?string $slug = 'profile';

    protected string $view = 'filament.pages.my-profile';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $profile = $user->profile;

        $user->loadMissing(['yayasan', 'perguruanTinggi', 'programStudis', 'roles']);

        $prodiList = $user->programStudis->pluck('nama_prodi')->implode(', ');
        $rolesList = $user->roles->pluck('name')->implode(', ');

        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'title_prefix' => $profile?->title_prefix,
            'title_suffix' => $profile?->title_suffix,
            'nidn' => $profile?->nidn,
            'nip' => $profile?->nip,
            'nik' => $profile?->nik,
            'gender' => $profile?->gender ?? 'male',
            'phone' => $profile?->phone,
            'functional_position' => $profile?->functional_position,
            'structural_position' => $profile?->structural_position,
            'expertise' => $profile?->expertise,
            'address' => $profile?->address,
            'bio' => $profile?->bio,
            // Info Read-Only Sistem
            'system_yayasan' => $user->yayasan?->nama ?? '—',
            'system_pt' => $user->perguruanTinggi?->nama_pt ?? '—',
            'system_prodi' => $prodiList ?: 'Tingkat Institusi',
            'system_roles' => $rolesList ?: 'Pengguna Terdaftar',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                // ── 1. Akun & Kredensial ──────────────────────────────────
                Section::make('Informasi Akun & Kredensial')
                    ->description('Kelola nama tampilan, alamat surel, dan kata sandi akun login Anda.')
                    ->icon('heroicon-o-user-circle')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap (Tampilan Akun)')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Alamat Surel (Email Login)')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('current_password')
                                    ->label('Kata Sandi Saat Ini')
                                    ->password()
                                    ->revealable()
                                    ->helperText('Wajib diisi hanya jika Anda ingin mengubah kata sandi.'),
                                TextInput::make('new_password')
                                    ->label('Kata Sandi Baru')
                                    ->password()
                                    ->revealable()
                                    ->minLength(8)
                                    ->helperText('Minimal 8 karakter.'),
                                TextInput::make('new_password_confirmation')
                                    ->label('Konfirmasi Kata Sandi Baru')
                                    ->password()
                                    ->revealable()
                                    ->same('new_password'),
                            ])
                            ->columnSpanFull(),
                    ]),
                // ── 2. Data Diri Lengkap & Akademik ────────────────────────
                Section::make('Data Diri Lengkap & Profil Akademik')
                    ->description('Lengkapi identitas akademik, nomor induk (NIDN/NIP), gelar, serta kontak pribadi Anda.')
                    ->icon('heroicon-o-identification')
                    ->columns(3)
                    ->schema([
                        TextInput::make('title_prefix')
                            ->label('Gelar Depan')
                            ->placeholder('Contoh: Prof., Dr., Ir.')
                            ->maxLength(50),
                        TextInput::make('title_suffix')
                            ->label('Gelar Belakang')
                            ->placeholder('Contoh: S.Kom., M.Kom., Ph.D.')
                            ->maxLength(50),
                        Radio::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'male' => 'Laki-laki',
                                'female' => 'Perempuan',
                            ])
                            ->inline(),
                        TextInput::make('nidn')
                            ->label('NIDN / NUP')
                            ->placeholder('Nomor Induk Dosen Nasional')
                            ->maxLength(50),
                        TextInput::make('nip')
                            ->label('NIP / NPK')
                            ->placeholder('Nomor Induk Pegawai')
                            ->maxLength(50),
                        TextInput::make('nik')
                            ->label('NIK (No. KTP)')
                            ->placeholder('16 digit NIK')
                            ->maxLength(30),
                        TextInput::make('phone')
                            ->label('Nomor WhatsApp / HP')
                            ->tel()
                            ->placeholder('08xxxxxxxxxx')
                            ->maxLength(30),
                        Select::make('functional_position')
                            ->label('Jabatan Fungsional')
                            ->options([
                                'Tenaga Pengajar' => 'Tenaga Pengajar',
                                'Asisten Ahli' => 'Asisten Ahli',
                                'Lektor' => 'Lektor',
                                'Lektor Kepala' => 'Lektor Kepala',
                                'Guru Besar / Profesor' => 'Guru Besar / Profesor',
                                'Tenaga Kependidikan' => 'Tenaga Kependidikan / Tendik',
                                'Staf Administrasi' => 'Staf Administrasi',
                            ])
                            ->searchable(),
                        TextInput::make('structural_position')
                            ->label('Jabatan Tambahan / Peran Mutu')
                            ->placeholder('Contoh: Kaprodi / Auditor AMI / Tim Akreditasi')
                            ->maxLength(100),
                        TextInput::make('expertise')
                            ->label('Bidang Keahlian / Fokus Riset')
                            ->placeholder('Contoh: Rekayasa Perangkat Lunak, Data Science')
                            ->columnSpanFull()
                            ->maxLength(255),
                        Textarea::make('address')
                            ->label('Alamat Domisili')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('bio')
                            ->label('Profil Singkat / Biografi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                // ── 3. Penugasan & Tenant Sistem (Read-Only) ────────────────
                Section::make('Informasi Penugasan & Hak Akses Sistem (Terkunci)')
                    ->description('Informasi organisasi dan peran sistem diatur oleh Administrator Institusi.')
                    ->icon('heroicon-o-lock-closed')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextInput::make('system_yayasan')
                            ->label('Yayasan')
                            ->disabled(),
                        TextInput::make('system_pt')
                            ->label('Perguruan Tinggi Terdaftar')
                            ->disabled(),
                        TextInput::make('system_prodi')
                            ->label('Program Studi')
                            ->disabled(),
                        TextInput::make('system_roles')
                            ->label('Peran / Role Pengguna')
                            ->disabled(),
                    ]),
            ]);
    }

    public function save(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $formData = $this->form->getState();

        // 1. Validasi Email Unik
        $emailExists = User::query()
            ->where('email', $formData['email'])
            ->where('id', '!=', $user->id)
            ->exists();

        if ($emailExists) {
            throw ValidationException::withMessages([
                'data.email' => 'Alamat email ini sudah digunakan oleh akun lain.',
            ]);
        }

        // 2. Validasi & Ganti Password jika diisi
        if (filled($formData['new_password'] ?? null)) {
            if (blank($formData['current_password'] ?? null) || !Hash::check($formData['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'data.current_password' => 'Kata sandi saat ini tidak sesuai.',
                ]);
            }

            $user->password = $formData['new_password'];
        }

        // 3. Simpan Akun User
        $user->name = $formData['name'];
        $user->email = $formData['email'];
        $user->save();

        // 4. Simpan Data Diri Profil
        UserProfile::query()->updateOrCreate([
            'user_id' => $user->id,
        ], [
            'title_prefix' => $formData['title_prefix'] ?? null,
            'title_suffix' => $formData['title_suffix'] ?? null,
            'nidn' => $formData['nidn'] ?? null,
            'nip' => $formData['nip'] ?? null,
            'nik' => $formData['nik'] ?? null,
            'gender' => $formData['gender'] ?? 'male',
            'phone' => $formData['phone'] ?? null,
            'functional_position' => $formData['functional_position'] ?? null,
            'structural_position' => $formData['structural_position'] ?? null,
            'expertise' => $formData['expertise'] ?? null,
            'address' => $formData['address'] ?? null,
            'bio' => $formData['bio'] ?? null,
        ]);

        Notification::make()
            ->title('Profil berhasil diperbarui.')
            ->body('Perubahan data diri dan akun Anda telah tersimpan dengan aman.')
            ->success()
            ->send();

        // Reset field password
        $this->data['current_password'] = null;
        $this->data['new_password'] = null;
        $this->data['new_password_confirmation'] = null;
    }
}
