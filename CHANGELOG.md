# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.10.0] - 2025-01-01

### Added
- 公開品質への全面刷新
- セキュリティ強化（nonce、サニタイズ、エスケープ処理、権限チェック）
- WordPress Coding Standards準拠（PHPCS自動修正適用済み）
- PHPStan静的解析対応（レベル6）
- 国際化対応（POTファイル・日本語翻訳・load_plugin_textdomain実装）
- アンインストール処理実装
- 開発者向けドキュメント整備（DEVELOPER.md、TESTING.md等）
- composer.json による開発環境整備
- GPLv2 or later ライセンスファイル追加

### Changed
- ファイル構造を再編成（`inc/` ディレクトリに整理）
- クラス設計をSingletonパターンに変更
- 機能をCore/Admin/Frontend に分割
- メイン関数名を `ns_mega_menu_render()` に変更
- バージョン定数を `NSMM_VERSION` に変更
- 管理画面JSをjQuery依存からVanilla JSに変更

### Fixed
- XSS脆弱性の修正（出力エスケープ強化）
- CSRF脆弱性の修正（nonce実装）
- 権限チェック追加
- 画像アップロード時の妥当性確認

### Security
- 全ての出力に適切なエスケープ処理を適用
- ファイルアップロード時の型チェック強化
- SQLインジェクション対策（prepare文使用）
- check_admin_referer() によるCSRF対策強化
- manage_options権限による管理機能アクセス制御

## [0.01] - 2024-XX-XX

### Added
- 初期実装
- 画像グリッド型メガメニュー
- ワイド型メガメニュー
- レスポンシブ対応
- 基本的なショートコード・テンプレートタグ

---

## 今後の予定

### [0.11.0] - 予定
- [ ] アクセシビリティ強化（ARIA属性追加）
- [ ] パフォーマンス最適化
- [ ] 管理画面UIの改善

### [0.12.0] - 予定  
- [ ] カスタムテンプレート対応
- [ ] 追加メガメニュータイプ
- [ ] アニメーション効果オプション

### [1.0.0] - 将来
- [ ] WordPress.orgディレクトリ公開
- [ ] Gutenbergブロック版実装
- [ ] REST API対応